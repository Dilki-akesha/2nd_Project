<?php
/**
 * Model query health check.
 *
 * A SQL error inside a model method is usually swallowed by a "return $result
 * ? ... : []" guard, which makes a broken query render as an empty page
 * instead of an error. This script invokes every read-only method on every
 * model, as each of the four roles, and reports anything that fails at the
 * SQL level (unknown column, bad join, missing table, ...).
 *
 * Usage: php scripts/check_model_queries.php
 */
require_once __DIR__ . '/../config/app.php';

$db = db();

/*
 * Each role is exercised with a real, approved, active demo account so that
 * role-scoped queries return the same rows the dashboards see.
 */
$roleFixtures = [
    'admin' => ['ADMIN', 'admin@gmail.com'],
    'buyer' => ['BUYER', 'buyer@gmail.com'],
    'farmer' => ['FARMER', 'farmer@gmail.com'],
    'courier' => ['COURIER_PARTNER', 'courier@gmail.com'],
];

$classes = [
    'OrderWorkflow'  => __DIR__ . '/../Model/OrderWorkflow.php',
    'AdminModel'     => __DIR__ . '/../Model/Admin/AdminModel.php',
    'AuthModel'      => __DIR__ . '/../Model/Auth/AuthModel.php',
    'Buyer/Cart'     => __DIR__ . '/../Model/Buyer/Cart.php',
    'Buyer/Checkout' => __DIR__ . '/../Model/Buyer/Checkout.php',
    'Buyer/Dashboard'=> __DIR__ . '/../Model/Buyer/Dashboard.php',
    'Buyer/Feedback' => __DIR__ . '/../Model/Buyer/Feedback.php',
    'Buyer/Notifs'   => __DIR__ . '/../Model/Buyer/Notifications.php',
    'Buyer/Orders'   => __DIR__ . '/../Model/Buyer/Orders.php',
    'Buyer/Product'  => __DIR__ . '/../Model/Buyer/Product.php',
    'Buyer/Profile'  => __DIR__ . '/../Model/Buyer/Profile.php',
    'Courier/Assign' => __DIR__ . '/../Model/Courier/AssignmentModel.php',
    'Courier/Main'   => __DIR__ . '/../Model/Courier/CourierModel.php',
    'Farmer/Main'    => __DIR__ . '/../Model/Farmer/FarmerModel.php',
    'Farmer/Orders'  => __DIR__ . '/../Model/Farmer/FarmerOrderModel.php',
    'Farmer/Products'=> __DIR__ . '/../Model/Farmer/FarmerProductModel.php',
    'Main/Product'   => __DIR__ . '/../Model/Main/ProductModel.php',
];

$problems = [];
$checked = 0;

function fixtureId(string $role, string $email): int {
    return (int)db_scalar('SELECT user_id FROM users WHERE email = ? LIMIT 1', 's', [$email], 0);
}

foreach ($roleFixtures as $roleKey => [$dbRole, $email]) {
    $userId = fixtureId($roleKey, $email);
    if ($userId <= 0) {
        $problems[] = "Demo account not found for role $roleKey ($email)";
        continue;
    }

    // Models call requireActiveRole() which reads straight from $_SESSION.
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $roleKey;
    $_SESSION['user_role'] = $dbRole;

    echo "\n=== $dbRole (user_id=$userId) ===\n";

    foreach ($classes as $label => $file) {
        if (!is_file($file)) {
            $problems[] = "$roleKey / $label: model file missing ($file)";
            continue;
        }
        require_once $file;

        $fqcn = array_values(array_filter(get_declared_classes(), function ($c) use ($label) {
            return substr($c, -strlen(str_replace('/', '\\', $label))) === str_replace('/', '\\', $label);
        }));
        // Fall back to "the class declared by this file".
        $before = get_declared_classes();
        require_once $file;
        $new = array_diff(get_declared_classes(), $before);
        $fqcn = $new ?: $fqcn;

        foreach ($fqcn as $class) {
            $rc = new ReflectionClass($class);
            if ($rc->isAbstract() || $rc->isInterface()) continue;
            try {
                $model = $rc->newInstance();
            } catch (Throwable $e) {
                $problems[] = "$class: cannot construct - " . $e->getMessage();
                continue;
            }

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                $name = $m->getName();
                if (str_starts_with($name, '__')) continue;
                if ($m->isStatic()) continue;
                if ($m->getNumberOfRequiredParameters() > 0) continue;
                // Only read methods; anything that mutates is left alone.
                if (!preg_match('/^(get|list|count|fetch|find|search|load|total|has|is|show|display|recent|pending|approved|my|all)/i', $name)) continue;

                $checked++;
                try {
                    $result = @$m->invoke($model);
                } catch (Throwable $e) {
                    $problems[] = "$roleKey / $class::$name() threw " . get_class($e) . ': ' . $e->getMessage();
                    continue;
                }

                $err = mysqli_error($db);
                if ($err !== '') {
                    $problems[] = "$roleKey / $class::$name() SQL error: $err";
                } else {
                    $count = is_array($result) ? count($result) : 'scalar';
                    printf("  %-46s ok  %s\n", $class . '::' . $name, $count);
                }
            }
        }
    }
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "Checked $checked read methods across 4 roles.\n";

if ($problems) {
    echo "\nPROBLEMS (" . count($problems) . "):\n";
    foreach ($problems as $p) echo "  - $p\n";
    exit(1);
}
echo "No failing queries.\n";
