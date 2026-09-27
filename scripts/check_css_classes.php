<?php
/**
 * Report CSS classes used in the views that no loaded stylesheet defines.
 *
 * A missing class silently drops the whole layout rule (a stat grid collapses
 * to a single tall column, for example), so this catches UI regressions that
 * the HTTP smoke test cannot see.
 *
 * Usage: php scripts/check_css_classes.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line only.');
}

$root = dirname(__DIR__);

/**
 * View directory => the stylesheets that view actually loads. Each actor has
 * its own shell, so a class defined for one actor does not cover another.
 */
$modules = [
    'Buyer'   => ['CSS/Buyer/shell.css'],
    'Farmer'  => ['CSS/Farmer/style.css', 'CSS/Farmer/shell-additions.css'],
    'Delivey' => ['CSS/Delivery/style.css', 'CSS/Delivery/shell-additions.css'],
    'Admin'   => ['CSS/Common/style.css'],
    'Main'    => ['CSS/Common/style.css'],
];

/**
 * Pull the `.class` selectors that a stylesheet really defines.
 *
 * A rule nested in a screen @media block with no top-level rule is not
 * counted: a responsive override on its own means the layout never applies at
 * the default width, which is the kind of bug this script is for. An
 * @media print block is a definition in its own right, so those count.
 */
function definedClasses(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $css = (string)file_get_contents($path);
    // Drop comments so commented-out rules do not count as defined.
    $css = (string)preg_replace('#/\*.*?\*/#s', '', $css);

    /*
     * Walk the file tracking brace depth so screen @media contents are skipped.
     * A selector prelude is collected until the opening brace; at depth 1 it is
     * a top-level rule, at depth 2 it belongs to the at-rule recorded in
     * $atRule (only an @media print block counts as a definition).
     */
    $depth = 0;
    $pending = '';
    $atRule = '';
    $base = [];
    $length = strlen($css);
    for ($i = 0; $i < $length; $i++) {
        $char = $css[$i];
        if ($char === '{') {
            $depth++;
            $prelude = trim($pending);
            $pending = '';
            if ($depth === 1 && strpos($prelude, '@') === 0) {
                $atRule = strtolower($prelude);
                continue;
            }
            if ($depth === 1 || ($depth === 2 && strpos($atRule, 'print') !== false)) {
                preg_match_all('/\.(-?[_a-zA-Z][_a-zA-Z0-9-]*)/', $prelude, $found);
                foreach ($found[1] as $name) {
                    $base[$name] = true;
                }
            }
            continue;
        }
        if ($char === '}') {
            $depth--;
            $pending = '';
            if ($depth === 0) {
                $atRule = '';
            }
            continue;
        }
        $pending .= $char;
    }
    return array_keys($base);
}

/** Pull every class="..." value out of the PHP views. */
function usedClasses(string $path): array
{
    $php = (string)file_get_contents($path);
    preg_match_all('/class\s*=\s*["\']([^"\']*)["\']/', $php, $matches);
    $classes = [];
    foreach ($matches[1] as $value) {
        foreach (preg_split('/\s+/', trim($value)) as $name) {
            if ($name !== '' && strpos($name, '$') === false && !preg_match('/[<>=?[]/', $name)) {
                $classes[] = $name;
            }
        }
    }
    return array_unique($classes);
}

// No stylesheet is shared between the actor shells, so nothing is carried
// over: a class must be defined by the sheets that view actually loads.
$known = [];

$problems = 0;
foreach ($modules as $module => $sheets) {
    $defined = [];
    foreach ($sheets as $sheet) {
        foreach (definedClasses($root . '/' . $sheet) as $name) {
            $defined[$name] = true;
        }
    }

    $missing = [];
    foreach (glob($root . '/View/' . $module . '/*.php') ?: [] as $view) {
        if (basename($view) === 'layout.php') {
            continue;
        }
        foreach (usedClasses($view) as $name) {
            if (isset($defined[$name]) || isset($known[$name])) {
                continue;
            }
            $missing[$name][] = basename($view);
        }
    }

    $label = str_pad($module, 8);
    if (!$missing) {
        echo "$label OK - every class used in the views is defined\n";
        continue;
    }
    $problems += count($missing);
    echo "$label " . count($missing) . " undefined class(es):\n";
    ksort($missing);
    foreach ($missing as $name => $views) {
        echo '         .' . $name . '  ->  ' . implode(', ', array_slice($views, 0, 4))
            . (count($views) > 4 ? ' (+' . (count($views) - 4) . ' more)' : '') . "\n";
    }
}

echo $problems === 0
    ? "\nRESULT: OK - no undefined CSS classes.\n"
    : "\nRESULT: $problems undefined CSS class(es) need a rule.\n";

exit($problems === 0 ? 0 : 1);
