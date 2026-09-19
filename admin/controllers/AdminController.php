<?php
/**
 * AdminController - Handles Admin Actions & Page Rendering
 */

require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../../includes/functions.php';

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
        $action = $_GET['admin_action'] ?? '';

        switch ($action) {
            case 'create_user':
                $role = sanitize($_POST['role'] ?? 'buyer');
                $data = [
                    'name' => sanitize($_POST['name'] ?? ''),
                    'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
                    'password' => $_POST['password'] ?? 'user123',
                    'phone' => sanitize($_POST['phone'] ?? ''),
                    'district' => sanitize($_POST['district'] ?? 'Colombo'),
                    'address' => sanitize($_POST['address'] ?? ''),
                    'nic_number' => sanitize($_POST['nic_number'] ?? ''),
                    'brn_number' => sanitize($_POST['brn_number'] ?? ''),
                    'contact_person' => sanitize($_POST['contact_person'] ?? ''),
                    'status' => sanitize($_POST['status'] ?? 'active')
                ];
                if ($data['email'] && !empty($data['name'])) {
                    $this->model->createUser($role, $data);
                    header("Location: index.php?page=admin_users&success=New+user+account+created+successfully.");
                } else {
                    header("Location: index.php?page=admin_users&error=Failed+to+create+user.+Invalid+input.");
                }
                exit();

            case 'update_user_details':
                $role = sanitize($_POST['role'] ?? '');
                $userId = intval($_POST['user_id'] ?? 0);
                $data = [
                    'name' => sanitize($_POST['name'] ?? ''),
                    'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
                    'phone' => sanitize($_POST['phone'] ?? ''),
                    'district' => sanitize($_POST['district'] ?? ''),
                    'address' => sanitize($_POST['address'] ?? ''),
                    'nic_number' => sanitize($_POST['nic_number'] ?? ''),
                    'brn_number' => sanitize($_POST['brn_number'] ?? ''),
                    'contact_person' => sanitize($_POST['contact_person'] ?? ''),
                    'status' => sanitize($_POST['status'] ?? 'active')
                ];
                if ($userId > 0 && $data['email']) {
                    $this->model->updateUserDetails($role, $userId, $data);
                    header("Location: index.php?page=admin_users&success=User+details+updated+successfully.");
                } else {
                    header("Location: index.php?page=admin_users&error=Failed+to+update+user.");
                }
                exit();

            case 'delete_user':
                $role = sanitize($_POST['role'] ?? '');
                $userId = intval($_POST['user_id'] ?? 0);
                if ($userId > 0) {
                    $this->model->deleteUser($role, $userId);
                    header("Location: index.php?page=admin_users&success=User+deleted+successfully.");
                }
                exit();

            case 'update_user_status':
                $userType = $_POST['user_type'] ?? '';
                $userId = intval($_POST['user_id'] ?? 0);
                $status = $_POST['status'] ?? '';
                $this->model->updateUserStatus($userType, $userId, $status);
                header("Location: index.php?page=admin_users&success=User+status+updated.");
                exit();

            case 'verify_farmer':
                $farmerId = intval($_POST['farmer_id'] ?? 0);
                $status = $_POST['status'] ?? 'approved';
                $reason = $_POST['rejection_reason'] ?? null;
                $this->model->updateFarmerVerification($farmerId, $status, $reason);
                header("Location: index.php?page=admin_verifications&success=Farmer+verification+updated.");
                exit();

            case 'verify_courier':
                $courierId = intval($_POST['courier_id'] ?? 0);
                $status = $_POST['status'] ?? 'approved';
                $reason = $_POST['rejection_reason'] ?? null;
                $this->model->updateCourierVerification($courierId, $status, $reason);
                header("Location: index.php?page=admin_verifications&success=Courier+verification+updated.");
                exit();

            case 'update_product_status':
                $productId = intval($_POST['product_id'] ?? 0);
                $status = $_POST['status'] ?? 'active';
                $this->model->updateProductStatus($productId, $status);
                header("Location: index.php?page=admin_listings&success=Product+status+updated.");
                exit();

            case 'override_delivery':
                $orderId = intval($_POST['order_id'] ?? 0);
                $courierId = intval($_POST['courier_id'] ?? 0);
                $this->model->overrideDeliveryAssignment($orderId, $courierId);
                header("Location: index.php?page=admin_orders&success=Courier+assignment+overridden.");
                exit();

            case 'resolve_dispute':
                $complaintId = intval($_POST['complaint_id'] ?? 0);
                $status = $_POST['status'] ?? 'resolved';
                $notes = $_POST['resolution_notes'] ?? '';
                $this->model->resolveComplaint($complaintId, $status, $notes);
                header("Location: index.php?page=admin_disputes&success=Dispute+resolved.");
                exit();

            case 'send_notification':
                $scope = $_POST['recipient_scope'] ?? 'all';
                $recId = !empty($_POST['recipient_id']) ? intval($_POST['recipient_id']) : null;
                $subject = sanitize($_POST['subject'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $this->model->createNotification($scope, $recId, $subject, $message);
                header("Location: index.php?page=admin_notifications&success=Notification+dispatched.");
                exit();

            case 'update_zone_fee':
                $tierId = intval($_POST['tier_id'] ?? 0);
                $baseFee = floatval($_POST['base_fee'] ?? 0);
                $perKgFee = floatval($_POST['per_kg_fee'] ?? 0);
                $this->model->updateZoneFeeTier($tierId, $baseFee, $perKgFee);
                header("Location: index.php?page=admin_regions_hubs&success=Zone+fee+matrix+updated.");
                exit();

            case 'update_settings':
                $settings = [
                    'commission_rate' => sanitize($_POST['commission_rate'] ?? '12.0'),
                    'tier_base_fee_western' => sanitize($_POST['tier_base_fee_western'] ?? '150.00'),
                    'tier_base_fee_central' => sanitize($_POST['tier_base_fee_central'] ?? '220.00'),
                    'tier_base_fee_southern' => sanitize($_POST['tier_base_fee_southern'] ?? '190.00'),
                    'tier_base_fee_northern' => sanitize($_POST['tier_base_fee_northern'] ?? '250.00'),
                    'auto_release_timeout_hours' => sanitize($_POST['auto_release_timeout_hours'] ?? '48')
                ];
                $this->model->updatePlatformSettings($settings);
                header("Location: index.php?page=admin_settings&success=Platform+settings+saved+to+database.");
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
                $activities = $this->model->getRecentActivityLog();
                require __DIR__ . '/../views/overview.php';
                break;

            case 'admin_users':
                $role = $_GET['role'] ?? 'all';
                $status = $_GET['status'] ?? 'all';
                $users = $this->model->getAllUsers($role, $status);
                require __DIR__ . '/../views/users.php';
                break;

            case 'admin_verifications':
                $pendingFarmers = $this->model->getPendingFarmers();
                $pendingCouriers = $this->model->getPendingCouriers();
                require __DIR__ . '/../views/verifications.php';
                break;

            case 'admin_listings':
                $products = $this->model->getAllProducts();
                require __DIR__ . '/../views/listings.php';
                break;

            case 'admin_orders':
                $orders = $this->model->getAllOrders();
                $couriers = $this->model->getAllUsers('courier', 'approved');
                require __DIR__ . '/../views/orders.php';
                break;

            case 'admin_disputes':
                $complaints = $this->model->getAllComplaints();
                require __DIR__ . '/../views/disputes.php';
                break;

            case 'admin_reports':
                $reportType = $_GET['type'] ?? 'orders';
                $startDate = $_GET['start'] ?? date('Y-m-01');
                $endDate = $_GET['end'] ?? date('Y-m-d');
                $reportData = $this->model->generateReportData($reportType, $startDate, $endDate);
                require __DIR__ . '/../views/reports.php';
                break;

            case 'admin_regions_hubs':
                $districts = $this->model->getDistricts();
                $tiers = $this->model->getZoneFeeTiers();
                $hubs = $this->model->getHubs();
                $coverage = $this->model->getCourierCoverage();
                require __DIR__ . '/../views/regions_hubs.php';
                break;

            case 'admin_settlements':
                $settlements = $this->model->getSettlements();
                require __DIR__ . '/../views/settlements.php';
                break;

            case 'admin_notifications':
                $logs = $this->model->getNotificationsLog();
                require __DIR__ . '/../views/notifications.php';
                break;

            case 'admin_settings':
                $settings = $this->model->getPlatformSettings();
                require __DIR__ . '/../views/settings.php';
                break;

            default:
                header("Location: index.php?page=admin_overview");
                exit();
        }
    }
}
