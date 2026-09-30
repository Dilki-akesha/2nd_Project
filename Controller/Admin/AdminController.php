<?php
/**
 * AdminController - Handles Admin Actions & Page Rendering
 */

require_once __DIR__ . '/../../Model/Admin/AdminModel.php';
require_once __DIR__ . '/../../config/helpers.php';

class AdminController {
    private $model;

    public function __construct() {
        checkAdminAuth();
        $this->model = new AdminModel();
    }

    /**
     * Dispatch POST actions for Admin operations
     */
    public function handleAdminAction() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(405); exit('POST required.'); }
        verifyCsrfToken();
        $action = $_GET['admin_action'] ?? '';

        switch ($action) {
            /* --- PRODUCT CATEGORIES (ADMIN MAIN CRUD) --- */
            case 'create_category':
                verifyCsrfToken();
                $name = sanitize($_POST['category_name'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');
                $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
                if ($name !== '' && mb_strlen($name) <= 100 && mb_strlen($desc) <= 500 &&
                    in_array($status, ['active', 'inactive'], true)) {
                    $ok = $this->model->createCategory($name, $desc, $status);
                    header("Location: index.php?page=admin_categories&" . ($ok ? 'success=Product+category+created+successfully.' : 'error=Unable+to+create+that+category.'));
                } else {
                    header("Location: index.php?page=admin_categories&error=Category+name+is+required.");
                }
                exit();

            case 'update_category':
                verifyCsrfToken();
                $id = intval($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['category_name'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');
                $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
                if ($id > 0 && $name !== '' && mb_strlen($name) <= 100 && mb_strlen($desc) <= 500 &&
                    in_array($status, ['active', 'inactive'], true)) {
                    $ok = $this->model->updateCategory($id, $name, $desc, $status);
                    header("Location: index.php?page=admin_categories&" . ($ok ? 'success=Product+category+updated+successfully.' : 'error=Unable+to+update+that+category.'));
                } else {
                    header("Location: index.php?page=admin_categories&error=Failed+to+update+category.");
                }
                exit();

            case 'deactivate_category':
                verifyCsrfToken();
                $id = intval($_POST['category_id'] ?? 0);
                if ($id > 0) {
                    $ok = $this->model->deactivateCategory($id);
                    header("Location: index.php?page=admin_categories&" . ($ok ? 'success=Category+deactivated.' : 'error=Unable+to+deactivate+that+category.'));
                }
                exit();

            case 'delete_category':
                verifyCsrfToken();
                $id = intval($_POST['category_id'] ?? 0);
                if ($id > 0) {
                    $ok = $this->model->deleteCategory($id);
                    header("Location: index.php?page=admin_categories&" . ($ok ? 'success=Category+deleted.' : 'error=Category+could+not+be+deleted+because+it+is+in+use.'));
                }
                exit();

            /* --- USER MANAGEMENT --- */
            case 'create_user':
                $role = sanitize($_POST['role'] ?? 'buyer');
                $password = (string)($_POST['password'] ?? '');
                if (strlen($password) < 8) {
                    header('Location: index.php?page=admin_users&error=' . urlencode('The initial password must be at least 8 characters long.'));
                    exit();
                }
                $data = [
                    'name' => trim((string)($_POST['name'] ?? '')),
                    'email' => trim((string)($_POST['email'] ?? '')),
                    'password' => $password,
                    'phone' => trim((string)($_POST['phone'] ?? '')),
                    'status' => strtoupper((string)($_POST['status'] ?? 'PENDING')),
                    'district' => trim((string)($_POST['district'] ?? '')),
                    'address' => trim((string)($_POST['address'] ?? '')),
                    'contact_person' => trim((string)($_POST['contact_person'] ?? '')),
                ];
                $allowedRoles = ['buyer', 'farmer', 'courier'];
                $allowedStatuses = ['PENDING', 'ACTIVE', 'SUSPENDED', 'REJECTED'];
                $phoneOk = $data['phone'] === '' || preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $data['phone']));
                $ok = in_array($role, $allowedRoles, true)
                    && in_array($data['status'], $allowedStatuses, true)
                    && mb_strlen($data['name']) >= 2 && mb_strlen($data['name']) <= 120
                    && filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                    && mb_strlen($data['email']) <= 254
                    && $phoneOk
                    && mb_strlen($data['address']) <= 180
                    && mb_strlen($data['contact_person']) <= 120
                    && $this->model->createUser($role, $data);
                header('Location: index.php?page=admin_users&' . ($ok
                    ? 'success=User+account+created.'
                    : 'error=The+account+could+not+be+created.+Check+the+name,+email+and+district.'));
                exit();

            case 'update_user_details':
                $role = sanitize($_POST['role'] ?? '');
                $userId = intval($_POST['user_id'] ?? 0);
                $data = [
                    'name' => trim((string)($_POST['name'] ?? '')),
                    'email' => trim((string)($_POST['email'] ?? '')),
                    'phone' => trim((string)($_POST['phone'] ?? '')),
                    'status' => strtoupper((string)($_POST['status'] ?? 'ACTIVE')),
                    'district' => trim((string)($_POST['district'] ?? '')),
                    'address' => trim((string)($_POST['address'] ?? '')),
                    'contact_person' => trim((string)($_POST['contact_person'] ?? '')),
                ];
                $allowedRoles = ['buyer', 'farmer', 'courier'];
                $allowedStatuses = ['PENDING', 'ACTIVE', 'SUSPENDED', 'REJECTED'];
                $phoneOk = $data['phone'] === '' || preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $data['phone']));
                $ok = $userId > 0
                    && in_array($role, $allowedRoles, true)
                    && in_array($data['status'], $allowedStatuses, true)
                    && mb_strlen($data['name']) >= 2 && mb_strlen($data['name']) <= 120
                    && filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                    && mb_strlen($data['email']) <= 254
                    && $phoneOk
                    && mb_strlen($data['address']) <= 180
                    && mb_strlen($data['contact_person']) <= 120
                    && $this->model->updateUserDetails($role, $userId, $data);
                header('Location: index.php?page=admin_users&' . ($ok
                    ? 'success=User+details+updated.'
                    : 'error=The+user+could+not+be+updated.'));
                exit();

            case 'delete_user':
                $role = sanitize($_POST['role'] ?? '');
                $userId = intval($_POST['user_id'] ?? 0);
                if ($userId > 0 && $userId !== (int)($_SESSION['user_id'] ?? 0)) {
                    $blocker = $this->model->deleteBlocker($userId);
                    if ($blocker !== '') {
                        header("Location: index.php?page=admin_users&error=" . urlencode($blocker));
                    } elseif ($this->model->deleteUser($role, $userId)) {
                        header("Location: index.php?page=admin_users&success=User+deleted.");
                    } else {
                        header("Location: index.php?page=admin_users&error=The+user+could+not+be+deleted.");
                    }
                } else {
                    header("Location: index.php?page=admin_users&error=You+cannot+delete+your+own+admin+account.");
                }
                exit();

            case 'update_user_status':
                $userType = $_POST['user_type'] ?? '';
                $userId = intval($_POST['user_id'] ?? 0);
                $status = strtoupper((string)($_POST['status'] ?? ''));
                if ($userId <= 0 || !in_array(strtolower((string)$userType), ['buyer', 'farmer', 'courier', 'courier_partner'], true) ||
                    !in_array($status, ['ACTIVE', 'SUSPENDED', 'PENDING', 'REJECTED'], true)) {
                    header("Location: index.php?page=admin_users&error=Invalid+user+status+or+user+id.");
                    exit();
                }
                $ok = $this->model->updateUserStatus($userType, $userId, $status);
                header("Location: index.php?page=admin_users&" . ($ok ? 'success=Account+status+updated.' : 'error=Invalid+account+status.'));
                exit();

            /* --- APPROVALS --- */
            case 'verify_farmer':
                $farmerId = intval($_POST['farmer_id'] ?? 0);
                $status = $_POST['status'] ?? 'approved';
                $reason = trim((string)($_POST['rejection_reason'] ?? ''));
                if ($farmerId <= 0 || !in_array($status, ['approved', 'rejected'], true) || mb_strlen($reason) > 500) {
                    header("Location: index.php?page=admin_farmer_approvals&error=Invalid+verification+data.");
                    exit();
                }
                $ok = $this->model->updateFarmerVerification($farmerId, $status, $reason !== '' ? $reason : null);
                header("Location: index.php?page=admin_farmer_approvals&" . ($ok ? 'success=Farmer+verification+updated.' : 'error=Farmer+could+not+be+updated.'));
                exit();

            case 'verify_courier':
                $courierId = intval($_POST['courier_id'] ?? 0);
                $status = $_POST['status'] ?? 'approved';
                $reason = trim((string)($_POST['rejection_reason'] ?? ''));
                if ($courierId <= 0 || !in_array($status, ['approved', 'rejected'], true) || mb_strlen($reason) > 500) {
                    header("Location: index.php?page=admin_courier_approvals&error=Invalid+verification+data.");
                    exit();
                }
                $ok = $this->model->updateCourierVerification($courierId, $status, $reason !== '' ? $reason : null);
                header("Location: index.php?page=admin_courier_approvals&" . ($ok ? 'success=Courier+Partner+verification+updated.' : 'error=Courier+Partner+could+not+be+updated.'));
                exit();

            /* --- LISTINGS & ORDERS --- */
            case 'update_product_status':
                $productId = intval($_POST['product_id'] ?? 0);
                // products.listing_status is an ENUM: ACTIVE, INACTIVE, EXPIRED, SOLD_OUT
                $status = strtoupper((string)($_POST['status'] ?? ''));
                if ($productId <= 0 || !in_array($status, ['ACTIVE', 'INACTIVE', 'EXPIRED', 'SOLD_OUT'], true)) {
                    header("Location: index.php?page=admin_listings&error=Invalid+listing+status+or+product.");
                    exit();
                }
                $ok = $this->model->updateProductStatus($productId, $status);
                header("Location: index.php?page=admin_listings&" . ($ok ? 'success=Listing+status+updated.' : 'error=Invalid+listing+status.'));
                exit();

            case 'override_delivery':
                $orderId = intval($_POST['order_id'] ?? 0);
                $courierId = intval($_POST['courier_id'] ?? 0);
                if ($orderId <= 0 || $courierId <= 0) {
                    header("Location: index.php?page=admin_pending_assignments&error=Invalid+order+or+courier.");
                    exit();
                }
                $ok = $this->model->overrideDeliveryAssignment($orderId, $courierId);
                header("Location: index.php?page=admin_pending_assignments&" . ($ok ? 'success=Courier+Partner+assignment+overridden.' : 'error=The+assignment+could+not+be+overridden.'));
                exit();

            /* --- DISTRICT DISTANCES --- */
            case 'add_district_distance':
                $from = sanitize($_POST['from_district'] ?? '');
                $to = sanitize($_POST['to_district'] ?? '');
                $km = filter_var($_POST['distance_km'] ?? null, FILTER_VALIDATE_FLOAT);
                if ($from === '' || $to === '' || $from === $to || $km === false || !is_finite((float)$km) || (float)$km <= 0 || (float)$km > 10000) {
                    header("Location: index.php?page=admin_district_distances&error=Enter+two+different+districts+and+a+valid+distance.");
                    exit();
                }
                $ok = $this->model->createDistrictDistance($from, $to, (float)$km);
                header("Location: index.php?page=admin_district_distances&" . ($ok ? 'success=District+distance+pair+added.' : 'error=Check+both+district+names+and+the+distance.'));
                exit();

            case 'update_district_distance':
                $id = intval($_POST['distance_id'] ?? 0);
                $from = sanitize($_POST['from_district'] ?? '');
                $to = sanitize($_POST['to_district'] ?? '');
                $km = filter_var($_POST['distance_km'] ?? null, FILTER_VALIDATE_FLOAT);
                if ($id <= 0 || $from === '' || $to === '' || $from === $to || $km === false || !is_finite((float)$km) || (float)$km <= 0 || (float)$km > 10000) {
                    header("Location: index.php?page=admin_district_distances&error=Enter+two+different+districts+and+a+valid+distance.");
                    exit();
                }
                $ok = $this->model->updateDistrictDistance($id, $from, $to, (float)$km);
                header("Location: index.php?page=admin_district_distances&" . ($ok ? 'success=District+distance+updated.' : 'error=The+distance+could+not+be+updated.'));
                exit();

            case 'delete_district_distance':
                $id = intval($_POST['distance_id'] ?? 0);
                $ok = $this->model->deleteDistrictDistance($id);
                header("Location: index.php?page=admin_district_distances&" . ($ok ? 'success=District+distance+removed.' : 'error=The+distance+could+not+be+removed.'));
                exit();

            /* --- COMPLAINTS, NOTIFICATIONS, SETTINGS & PROFILE --- */
            case 'resolve_complaint':
                $complaintId = intval($_POST['complaint_id'] ?? 0);
                // complaints.complaint_status is an ENUM: OPEN, UNDER_REVIEW, RESOLVED, REJECTED
                $status = strtoupper($_POST['status'] ?? 'RESOLVED');
                $notes = trim((string)($_POST['admin_response'] ?? ''));
                if ($complaintId <= 0 || mb_strlen($notes) > 5000 || !in_array($status, ['RESOLVED', 'REJECTED', 'UNDER_REVIEW'], true)) {
                    header("Location: index.php?page=admin_complaints&error=Invalid+complaint+update.");
                    exit();
                }
                $ok = in_array($status, ['RESOLVED', 'REJECTED', 'UNDER_REVIEW'], true)
                    && $this->model->resolveComplaint($complaintId, $status, $notes);
                header("Location: index.php?page=admin_complaints&" . ($ok ? 'success=Complaint+updated.' : 'error=The+complaint+could+not+be+updated.'));
                exit();

            case 'send_notification':
                $scope = (string)($_POST['recipient_scope'] ?? 'all');
                $recId = !empty($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
                $subject = trim((string)($_POST['subject'] ?? ''));
                $message = trim((string)($_POST['message'] ?? ''));
                $validScopes = ['all', 'buyers', 'farmers', 'couriers', 'user'];
                if (!in_array($scope, $validScopes, true) || ($scope === 'user' && $recId <= 0) ||
                    $subject === '' || mb_strlen($subject) > 200 || $message === '' || mb_strlen($message) > 5000) {
                    header("Location: index.php?page=admin_notifications&error=Check+recipient,+subject+and+message.");
                    exit();
                }
                $created = $this->model->createNotification($scope, $recId, $subject, $message);
                header("Location: index.php?page=admin_notifications&" . ($created > 0
                    ? 'success=Notification+sent+to+' . $created . '+recipient(s).'
                    : 'error=The+notification+was+not+sent.+Check+the+scope,+subject+and+message.'));
                exit();

            case 'update_settings':
                $settings = [
                    'farmer_marketplace_fee_percent' => trim((string)($_POST['farmer_marketplace_fee_percent'] ?? '')),
                    'buyer_service_fee_percent' => trim((string)($_POST['buyer_service_fee_percent'] ?? '')),
                    'courier_assignment_response_minutes' => trim((string)($_POST['courier_assignment_response_minutes'] ?? '')),
                    'delivery_base_fee' => trim((string)($_POST['delivery_base_fee'] ?? '')),
                    'delivery_per_km_rate' => trim((string)($_POST['delivery_per_km_rate'] ?? '')),
                ];
                $feeFields = ['farmer_marketplace_fee_percent', 'buyer_service_fee_percent'];
                foreach ($feeFields as $field) {
                    if (!is_numeric($settings[$field]) || (float)$settings[$field] < 0 || (float)$settings[$field] > 100) {
                        header("Location: index.php?page=admin_settings&error=Fee+percentages+must+be+between+0+and+100.");
                        exit();
                    }
                }
                if (!is_numeric($settings['courier_assignment_response_minutes']) || (int)$settings['courier_assignment_response_minutes'] < 5 || (int)$settings['courier_assignment_response_minutes'] > 10080 ||
                    !is_numeric($settings['delivery_base_fee']) || (float)$settings['delivery_base_fee'] < 0 ||
                    !is_numeric($settings['delivery_per_km_rate']) || (float)$settings['delivery_per_km_rate'] < 0) {
                    header("Location: index.php?page=admin_settings&error=Enter+valid+non-negative+delivery+settings.");
                    exit();
                }
                $result = $this->model->updatePlatformSettings($settings, (int)($_SESSION['user_id'] ?? 1));
                header("Location: index.php?page=admin_settings&" . ($result === true
                    ? 'success=Platform+settings+saved.'
                    : 'error=' . urlencode((string)$result)));
                exit();

            case 'update_admin_profile':
                $adminId = $_SESSION['user_id'] ?? 1;
                $name = trim((string)($_POST['full_name'] ?? ''));
                $email = trim((string)($_POST['email'] ?? ''));
                $pass = trim((string)($_POST['new_password'] ?? ''));
                $confirm = trim((string)($_POST['confirm_password'] ?? ''));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
                    header("Location: index.php?page=admin_profile&error=Enter+a+valid+name+and+email+address.");
                    exit();
                }
                if ($pass !== '' && (strlen($pass) < 8 || $pass !== $confirm)) {
                    header("Location: index.php?page=admin_profile&error=The+new+password+must+be+at+least+8+characters+and+both+fields+must+match.");
                    exit();
                }
                if ($this->model->updateAdminProfile($adminId, $name, $email, $pass !== '' ? $pass : null)) {
                    $_SESSION['user_name'] = $name;
                    header("Location: index.php?page=admin_profile&success=Profile+updated.");
                } else {
                    header("Location: index.php?page=admin_profile&error=The+profile+could+not+be+updated.");
                }
                exit();

            default:
                header("Location: index.php?page=admin_overview&error=Unknown+Admin+action.");
                exit();
        }
    }

    /**
     * Render Admin Dashboard Views
     */
    public function renderView($viewName) {
        switch ($viewName) {
            case 'admin_overview':
                $kpis = $this->model->getOverviewKPIs();
                $weeklyOrders=$this->model->getWeeklyOrderCounts();
                $activities = $this->model->getRecentActivityLog();
                require __DIR__ . '/../../View/Admin/overview.php';
                break;

            case 'admin_users':
                $role = $_GET['role'] ?? 'all';
                $status = $_GET['status'] ?? 'all';
                $users = $this->model->getAllUsers($role, $status);
                $districts = $this->model->getDistricts();
                require __DIR__ . '/../../View/Admin/users.php';
                break;

            case 'admin_farmer_approvals':
                $pendingFarmers = $this->model->getPendingFarmers();
                require __DIR__ . '/../../View/Admin/farmer_approvals.php';
                break;

            case 'admin_courier_approvals':
                $pendingCouriers = $this->model->getPendingCouriers();
                require __DIR__ . '/../../View/Admin/courier_approvals.php';
                break;

            case 'admin_listings':
                $products = $this->model->getAllProducts();
                require __DIR__ . '/../../View/Admin/listings.php';
                break;

            case 'admin_categories': // Admin CRUD Feature
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? 'all';
                $categories = $this->model->getAllCategories($search, $status);
                require __DIR__ . '/../../View/Admin/categories.php';
                break;

            case 'admin_orders':
                $orders = $this->model->getAllOrders();
                $couriers = $this->model->getApprovedCouriers();
                require __DIR__ . '/../../View/Admin/orders.php';
                break;

            case 'admin_deliveries':
                $deliveries = $this->model->getAllDeliveries();
                require __DIR__ . '/../../View/Admin/deliveries.php';
                break;

            case 'admin_pending_assignments':
                $pendingOrders = $this->model->getPendingAssignments();
                $couriers = $this->model->getApprovedCouriers();
                require __DIR__ . '/../../View/Admin/pending_assignments.php';
                break;

            case 'admin_district_distances':
                $fromDistrict = sanitize($_GET['from_district'] ?? '');
                $toDistrict = sanitize($_GET['to_district'] ?? '');
                $distances = $this->model->getAllDistrictDistances($fromDistrict, $toDistrict);
                $allDistricts = $this->model->getAllDistricts();
                require __DIR__ . '/../../View/Admin/district_distances.php';
                break;

            case 'admin_complaints':
                $complaints = $this->model->getAllComplaints();
                require __DIR__ . '/../../View/Admin/complaints.php';
                break;

            case 'admin_payments':
                $payments = $this->model->getAllPayments();
                require __DIR__ . '/../../View/Admin/payments.php';
                break;

            case 'admin_settlements':
                $settlements = $this->model->getSettlements();
                $settlementSummary = $this->model->getSettlementSummary();
                require __DIR__ . '/../../View/Admin/settlements.php';
                break;

            case 'admin_notifications':
                $logs = $this->model->getNotificationsLog();
                require __DIR__ . '/../../View/Admin/notifications.php';
                break;

            case 'admin_settings':
                $settings = $this->model->getPlatformSettings();
                require __DIR__ . '/../../View/Admin/settings.php';
                break;

            case 'admin_reports':
                $reportType = $_GET['type'] ?? 'orders';
                $startDate = $_GET['start'] ?? date('Y-m-01');
                $endDate = $_GET['end'] ?? date('Y-m-d');
                $reportData = $this->model->generateReportData($reportType, $startDate, $endDate);
                require __DIR__ . '/../../View/Admin/reports.php';
                break;

            case 'admin_profile':
                $adminProfile = $this->model->getAdminProfile($_SESSION['user_id'] ?? 1);
                require __DIR__ . '/../../View/Admin/profile.php';
                break;

            default:
                header("Location: index.php?page=admin_overview");
                exit();
        }
    }
}
