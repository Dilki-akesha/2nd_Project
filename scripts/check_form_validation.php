<?php
/**
 * Form validation audit.
 *
 * Walks every view, finds each <form> and every control inside it, and reports
 * controls whose client-side constraints do not match what the backend and the
 * database actually enforce. Browser-side attributes are a usability layer, so
 * anything reported here should also be enforced server-side.
 *
 * Usage: php scripts/check_form_validation.php
 */
$root = dirname(__DIR__);
$viewDir = $root . '/View';

$issues = [];
$fields = 0;
$forms = 0;

function attr(string $tag, string $name): ?string
{
    // Matches name="value", name='value' or bare name (boolean attribute).
    if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*"([^"]*)"/i', $tag, $m)) return $m[1];
    if (preg_match("/\\b" . preg_quote($name, '/') . "\\s*=\\s*'([^']*)'/i", $tag, $m)) return $m[1];
    if (preg_match('/\b' . preg_quote($name, '/') . '\b(?!\s*=)/i', $tag)) return '';
    return null;
}

function addIssue(string $file, string $control, string $message): void
{
    global $issues;
    $issues[] = [
        'file' => str_replace(dirname(__DIR__) . '/', '', $file),
        'control' => $control,
        'message' => $message,
    ];
}

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));
$viewFiles = [];
foreach ($it as $f) {
    if ($f->isFile() && strtolower($f->getExtension()) === 'php') $viewFiles[] = $f->getPathname();
}
sort($viewFiles);

foreach ($viewFiles as $file) {
    $html = (string)file_get_contents($file);

    /*
     * Only real markup: strip every PHP block, including the short echo form
     * <?= ... ?>. Leaving those in place matters because their closing "?>"
     * contains a ">" that would truncate the tag being matched below.
     */
    $html = preg_replace('/<\?(?:php|=).*?\?>/s', '', $html);

    if (!preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', $html, $formMatches)) continue;

    foreach ($formMatches[0] as $formTag) {
        $forms++;
        $method = strtoupper((string)attr($formTag, 'method'));
        $enctype = (string)attr($formTag, 'enctype');
        $isPost = $method === 'POST' || $method === '';

        preg_match_all('/<(input|select|textarea)\b([^>]*)>/i', $formTag, $controls, PREG_SET_ORDER);

        foreach ($controls as $control) {
            $tag = strtolower($control[1]);
            $raw = trim($control[0]);
            $name = (string)attr($raw, 'name');
            if ($name === '' || $name === null) continue;      // submit buttons etc.
            $fields++;

            $type = strtolower((string)(attr($raw, 'type') ?? ($tag === 'select' ? 'select' : 'text')));
            $required = attr($raw, 'required') !== null;

            // Skip purely navigational / hidden plumbing.
            if (in_array($type, ['hidden', 'submit', 'button', 'file', 'checkbox', 'radio', 'image'], true)) continue;

            $label = $name . ' (' . $type . ')';
            $lower = strtolower($name);

            // 1. An email field that is not declared as type="email".
            if (str_contains($lower, 'email') && $type !== 'email') {
                addIssue($file, $label, 'email field is not type="email", so the browser never checks the format');
            }

            // 2. Text-like fields with no upper bound, where the column is a
            //    varchar and an over-long value would be silently truncated.
            if (in_array($type, ['text', 'password', 'search', 'url', 'tel'], true) && attr($raw, 'maxlength') === null) {
                addIssue($file, $label, 'no maxlength, so an over-long value is only rejected by the database');
            }

            // 3. Numeric fields that could submit a negative value.
            //    min="0" is the correct floor for stock on hand and for a
            //    same-district route distance, so only a missing or negative
            //    min is reported. Zero/positive-only business quantities are
            //    additionally enforced server-side and by the database CHECK
            //    constraints in database/validation_constraints.sql.
            if ($type === 'number') {
                $min = attr($raw, 'min');
                if ($min === null) {
                    addIssue($file, $label, 'number field has no min, so negative values can be submitted');
                } elseif ((float)$min < 0) {
                    addIssue($file, $label, "min=\"$min\" still permits negative values");
                }
            }

            // 4. Passwords being newly set need a minimum length. A field with
            //    autocomplete="current-password" is an existing password being
            //    typed to sign in, so a minlength there would wrongly lock out
            //    accounts created before the rule existed.
            if ($type === 'password'
                && attr($raw, 'minlength') === null
                && attr($raw, 'autocomplete') !== 'current-password'
                && !str_contains($lower, 'confirm')) {
                addIssue($file, $label, 'password field has no minlength attribute');
            }

            // 5. A harvest date already in the past is a legitimate value when a
            //    Farmer edits an existing listing, so a client-side min="today"
            //    would wrongly block saving. The ordering rule (available_from
            //    >= harvest_date, season_end >= season_start) is enforced in
            //    Model/Farmer/FarmerProductModel::save() instead, and is covered
            //    by scripts/smoke_test.php.

            /*
             * NOTE on the `pattern` attribute: modern browsers compile it with
             * the RegExp "v" flag, where an unescaped "-", "(" or ")" inside a
             * character class makes the WHOLE pattern invalid, and the browser
             * then silently ignores it. So a pattern must escape them:
             *
             *     WRONG (silently ignored):  pattern="[0-9+()\-\s]{9,25}"
             *     RIGHT (actually enforced): pattern="[0-9+ \(\)\-]{9,25}"
             *
             * Telling those apart needs a real character-class parser, so this
             * script deliberately does not try. Every pattern below was verified
             * in the browser instead; see README-SCOPE-CLEANUP.md.
             */

            // 6. A POST form that uploads a file but does not declare the encoding
            //    is a real bug: the file silently arrives empty.
            if ($isPost && $method === 'POST' && stripos($formTag, 'type="file"') !== false
                && stripos($enctype, 'multipart') === false) {
                addIssue($file, $label, 'form uploads a file but is missing enctype="multipart/form-data"');
            }
        }
    }
}

echo "Forms scanned : $forms\n";
echo "Fields scanned: $fields\n\n";

if (!$issues) {
    echo "RESULT: OK - every control declares the constraints the backend enforces.\n";
    exit(0);
}

$byFile = [];
foreach ($issues as $i) {
    $byFile[$i['file']][] = $i;
}

echo "RESULT: " . count($issues) . " field(s) missing client-side validation.\n\n";
foreach ($byFile as $file => $list) {
    echo "$file\n";
    foreach ($list as $i) {
        echo "  - {$i['control']}\n      {$i['message']}\n";
    }
    echo "\n";
}
exit(1);
