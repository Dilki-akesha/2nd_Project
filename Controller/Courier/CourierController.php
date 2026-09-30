<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Courier/CourierModel.php';

requireCourierAuth();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') verifyCsrfToken();

$model = new CourierModel();
$courierId = currentCourierId();

/* The Courier Partner is an organisation. The view name keeps the original
   folder name (View/Delivey) so existing paths do not break. */
$allowedPages = [
    'dashboard', 'requests', 'assigned', 'tracking', 'history',
    'earnings', 'complaints', 'coverage', 'notifications', 'profile',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $ok = false;
    $message = 'Unknown action.';

    switch ($action) {
        case 'availability':
            $status = (string)($_POST['status'] ?? 'UNAVAILABLE') === 'AVAILABLE' ? 'AVAILABLE' : 'UNAVAILABLE';
            $ok = $model->setAvailability($courierId, $status);
            $message = $ok
                ? 'You are now marked as ' . strtolower($status) . '.'
                : 'Unable to update availability.';
            break;

        case 'add_route':
            $origin = (int)($_POST['origin_district_id'] ?? 0);
            $destination = (int)($_POST['destination_district_id'] ?? 0);
            if ($origin <= 0 || $destination <= 0 || $origin === $destination) {
                $message = 'Please choose two different valid districts.';
                break;
            }
            $ok = $model->addRoute(
                $courierId,
                $origin,
                $destination
            );
            $message = $ok ? 'Coverage route added.' : 'Unable to save route. Choose two valid active districts.';
            break;

        case 'update_route':
            $routeId = (int)($_POST['route_id'] ?? 0);
            $origin = (int)($_POST['origin_district_id'] ?? 0);
            $destination = (int)($_POST['destination_district_id'] ?? 0);
            if ($routeId <= 0 || $origin <= 0 || $destination <= 0 || $origin === $destination) {
                $message = 'Please choose a valid route and two different districts.';
                break;
            }
            $ok = $model->updateRoute(
                $courierId,
                $routeId,
                $origin,
                $destination,
                isset($_POST['is_active'])
            );
            $message = $ok ? 'Coverage route updated.' : 'Unable to update route. Check the districts and try again.';
            break;

        case 'delete_route':
            $routeId = (int)($_POST['route_id'] ?? 0);
            if ($routeId <= 0) {
                $message = 'Invalid coverage route.';
                break;
            }
            $ok = $model->deleteRoute($courierId, $routeId);
            $message = $ok ? 'Coverage route removed.' : 'Unable to remove that route.';
            break;

        case 'respond_offer':
            $offerId = (int)($_POST['offer_id'] ?? 0);
            $response = strtolower(trim((string)($_POST['response'] ?? '')));
            if ($offerId <= 0 || !in_array($response, ['accept', 'reject'], true)) {
                $message = 'Please choose a valid assignment response.';
                break;
            }
            [$ok, , $message] = $model->respondOffer(
                $courierId,
                $offerId,
                $response
            );
            break;

        case 'delivery_status':
            $deliveryId = (int)($_POST['delivery_id'] ?? 0);
            $nextStatus = trim((string)($_POST['next_status'] ?? ''));
            if ($deliveryId <= 0 || $nextStatus === '' || mb_strlen($nextStatus) > 40) {
                $message = 'Invalid delivery status update.';
                break;
            }
            [$ok, $message] = $model->updateDeliveryStatus(
                $courierId,
                $deliveryId,
                $nextStatus
            );
            break;

        case 'buyer_unavailable':
            $deliveryId = (int)($_POST['delivery_id'] ?? 0);
            $notes = trim((string)($_POST['notes'] ?? ''));
            if ($deliveryId <= 0 || mb_strlen($notes) > 500) {
                $message = 'Please provide valid delivery notes (500 characters or fewer).';
                break;
            }
            [$ok, $message] = $model->buyerUnavailable(
                $courierId,
                $deliveryId,
                $notes
            );
            break;

        case 'complaint':
            $orderId = (int)($_POST['order_id'] ?? 0);
            $category = trim((string)($_POST['category'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            if ($orderId <= 0 || !in_array($category, ['Delivery issue', 'Payment issue', 'Product issue', 'Other'], true) ||
                $description === '' || mb_strlen($description) > 5000) {
                $message = 'Please select a valid order/category and enter an issue description.';
                break;
            }
            $evidence = null;
            if (!empty($_FILES['evidence']['name'])) {
                try {
                    $evidence = handleFileUpload($_FILES['evidence'], 'assets/documents/complaints');
                } catch (Throwable $uploadError) {
                    $message = $uploadError->getMessage();
                    break;
                }
            }
            $ok = $model->addComplaint(
                $courierId,
                $orderId,
                $category,
                $description,
                $evidence
            );
            $message = $ok ? 'Issue submitted. Harvestly Admin will review it.' : 'Unable to submit issue. Please complete the category and description.';
            break;

        case 'mark_notifications_read':
            $ok = $model->markNotificationsRead($courierId);
            $message = $ok ? 'Notifications marked as read.' : 'Unable to update notifications.';
            break;

        case 'profile':
            $data = [
                'organisation_name' => trim((string)($_POST['organisation_name'] ?? '')),
                'contact_person_name' => trim((string)($_POST['contact_person_name'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'office_address_line1' => trim((string)($_POST['office_address_line1'] ?? '')),
                'office_address_line2' => trim((string)($_POST['office_address_line2'] ?? '')),
                'office_city_town' => trim((string)($_POST['office_city_town'] ?? '')),
                'office_postal_code' => trim((string)($_POST['office_postal_code'] ?? '')),
                'office_district_id' => (int)($_POST['office_district_id'] ?? 0),
            ];
            if ($data['organisation_name'] === '' || mb_strlen($data['organisation_name']) > 160 ||
                $data['contact_person_name'] !== '' && mb_strlen($data['contact_person_name']) > 120) {
                $message = 'Please enter valid organisation and contact details.';
            } elseif (!preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $data['phone']))) {
                $message = 'Please enter a valid Sri Lankan phone number.';
            } elseif (mb_strlen($data['office_address_line1']) > 180 || mb_strlen($data['office_address_line2']) > 180 ||
                      mb_strlen($data['office_city_town']) > 100 || ($data['office_postal_code'] !== '' && !preg_match('/^\d{5}$/', $data['office_postal_code']))) {
                $message = 'Please check the office address and postal code.';
            } elseif ($data['office_district_id'] <= 0) {
                $message = 'Please select an active office district.';
            } else {
                $ok = $model->updateProfile($courierId, $data);
                $message = $ok ? 'Organisation profile updated.' : 'Unable to update profile.';
            }
            break;
    }

    $_SESSION['_courier_flash'] = ['ok' => $ok, 'message' => $message];

    // The return page is re-validated against the whitelist on the next request.
    $returnPage = (string)($_POST['return_page'] ?? 'dashboard');
    if (!in_array($returnPage, $allowedPages, true)) $returnPage = 'dashboard';
    redirect('Controller/Courier/CourierController.php?page=' . urlencode($returnPage));
}

$page = (string)($_GET['page'] ?? 'dashboard');
if (!in_array($page, $allowedPages, true)) $page = 'dashboard';

$profile = $model->profile($courierId);
$stats = $model->stats($courierId);
$districts = $model->districts();
$flash = $_SESSION['_courier_flash'] ?? null;
unset($_SESSION['_courier_flash']);

$data = [];
switch ($page) {
    case 'dashboard':
        $data = ['offers' => $model->offers($courierId, 5)];
        break;
    case 'requests':
        $data = ['offers' => $model->offers($courierId)];
        break;
    case 'assigned':
        $data = ['deliveries' => $model->activeDeliveries($courierId)];
        break;
    case 'tracking':
        $data = ['delivery' => $model->delivery($courierId, (int)($_GET['id'] ?? 0))];
        break;
    case 'history':
        $data = ['history' => $model->history($courierId)];
        break;
    case 'earnings':
        $data = ['earnings' => $model->earnings($courierId), 'totals' => $model->earningsTotals($courierId)];
        break;
    case 'complaints':
        $data = ['complaints' => $model->complaints($courierId), 'orders' => $model->complaintOrders($courierId)];
        break;
    case 'coverage':
        $data = ['routes' => $model->routes($courierId)];
        break;
    case 'notifications':
        $data = ['notifications' => $model->notifications($courierId)];
        break;
    case 'profile':
    default:
        break;
}

require __DIR__ . '/../../View/Delivey/index.php';
