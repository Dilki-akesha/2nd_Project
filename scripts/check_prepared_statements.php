<?php
/**
 * Harvestly static check: prepared-statement placeholder / bind-type parity.
 *
 * Catches the class of bug where a SQL statement has a different number of
 * placeholders than the bind type string has characters, which silently breaks
 * every write. Runs without a database connection.
 *
 * Usage: php scripts/check_prepared_statements.php
 */

$root = dirname(__DIR__);
$errors = [];
$checked = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$files = [];
foreach ($iterator as $file) {
    $path = $file->getPathname();
    if (substr($path, -4) !== '.php') continue;
    if (strpos($path, DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR) !== false) continue;
    $files[] = $path;
}
sort($files);

/** Count SQL placeholders in a SQL string (ignores ? inside quoted literals). */
function countPlaceholders(string $sql): int
{
    $count = 0;
    $inSingle = false;
    $inDouble = false;
    $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        if ($ch === "'" && !$inDouble) {
            // Handle escaped '' pairs.
            if ($inSingle && $i + 1 < $len && $sql[$i + 1] === "'") {
                $i++;
                continue;
            }
            $inSingle = !$inSingle;
            continue;
        }
        if ($ch === '"' && !$inSingle) {
            $inDouble = !$inDouble;
            continue;
        }
        if ($ch === '?' && !$inSingle && !$inDouble) $count++;
    }
    return $count;
}

foreach ($files as $path) {
    $src = file_get_contents($path);
    if ($src === false) continue;
    $tokens = @token_get_all($src);
    if (!$tokens) continue;

    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    $line = 0;
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $tok = $tokens[$i];
        if (!is_array($tok)) continue;
        if ($tok[0] === T_LINE) {
            $line = $tok[2];
            continue;
        }
        if ($tok[0] !== T_STRING) continue;

        $fn = $tok[1];
        if (!in_array($fn, ['db_execute', 'db_fetch_one', 'db_fetch_all', 'db_scalar'], true)) continue;

        // Collect the call arguments by tracking parenthesis depth.
        $j = $i + 1;
        while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        if ($j >= $count || $tokens[$j] !== '(') continue;

        $depth = 0;
        $args = [];
        $current = '';
        for ($k = $j; $k < $count; $k++) {
            $t = $tokens[$k];
            $text = is_array($t) ? $t[1] : $t;
            if ($text === '(' || $text === '[') $depth++;
            if ($text === ')' || $text === ']') {
                $depth--;
                if ($depth === 0) {
                    $args[] = trim($current);
                    break;
                }
            }
            if ($text === ',' && $depth === 1) {
                $args[] = trim($current);
                $current = '';
                continue;
            }
            $current .= $text;
        }

        if (count($args) < 3) continue;

        $checked++;

        // Argument 1: SQL. Argument 2: bind types. Argument 3: params.
        $sqlLiteral = $args[0];
        $typeLiteral = $args[1];
        $paramLiteral = $args[2];

        // Only static, literal forms can be verified without running the code.
        if ($sqlLiteral === '' || $sqlLiteral[0] !== "'" || $typeLiteral === '' || $typeLiteral[0] !== "'") {
            continue;
        }
        $sql = substr($sqlLiteral, 1, -1);
        $types = substr($typeLiteral, 1, -1);

        // The param argument must be a statically countable array literal.
        if ($paramLiteral === '' || $paramLiteral[0] !== '[') continue;
        $inner = trim(substr($paramLiteral, 1, strrpos($paramLiteral, ']') - 1));
        $params = $inner === '' ? 0 : count(preg_split('/,(?![^\[]*\])/', $inner));

        $placeholders = countPlaceholders($sql);
        $typeCount = strlen($types);

        if ($placeholders !== $typeCount) {
            $errors[] = "$rel: SQL has $placeholders placeholder(s) but the bind type string has $typeCount character(s)";
            continue;
        }
        if ($typeCount !== $params) {
            $errors[] = "$rel: bind type string has $typeCount character(s) but $params value(s) are passed";
        }
    }
}

echo "Checked $checked prepared-statement call site(s) across " . count($files) . " PHP file(s).\n";

if ($errors) {
    echo "\nMISMATCHES FOUND:\n";
    foreach (array_unique($errors) as $e) echo "  - $e\n";
    echo "\nRESULT: FAILED\n";
    exit(1);
}

echo "RESULT: OK - every static prepared statement has matching placeholders, types and values.\n";
exit(0);
