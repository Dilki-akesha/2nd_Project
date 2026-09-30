<?php
/**
 * Harvestly common authentication controller.
 *
 * ONE authentication system serves all four actors:
 *   Buyer            - public registration, active immediately
 *   Farmer           - public registration, Pending until an Admin approves
 *   Courier Partner  - public organisation registration, Pending until approved
 *   Admin            - no public registration
 *
 * Core PHP + MySQLi only. No external authentication service.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Auth/AuthModel.php';

class AuthController
{
    private AuthModel $model;

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    /** Absolute URL for a role's home screen, used after login. */
    private function homeFor(string $role): string
    {
        return match ($role) {
            'admin'   => 'index.php?page=admin_overview',
            'buyer'   => 'Controller/Buyer/DashboardController.php',
            'farmer'  => 'View/Farmer/dashboard.php',
            'courier' => 'Controller/Courier/CourierController.php?page=dashboard',
            default   => 'index.php',
        };
    }

    /* --------------------------------- Login -------------------------------- */

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=login');
        }

        verifyCsrfToken();

        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string)($_POST['password'] ?? '');

        if (!$email || $password === '') {
            redirect('index.php?page=login&error=' . urlencode('Please enter your email address and password.'));
        }

        $user = $this->model->findUserByEmail($email);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            redirect('index.php?page=login&error=' . urlencode('Invalid email address or password.'));
        }

        // Suspended / rejected accounts can never reach a dashboard.
        $status = (string)$user['status'];
        if ($status === 'pending') {
            redirect('index.php?page=pending_approval&message=' . urlencode(
                'Your account is awaiting Admin approval. You will be able to sign in once an Admin approves it.'
            ));
        }
        if ($status !== 'approved') {
            redirect('index.php?page=login&error=' . urlencode(
                'This account is ' . $status . '. Please contact the Harvestly administrator.'
            ));
        }

        // Rehash if the algorithm or cost has changed.
        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_BCRYPT)) {
            $this->model->updatePassword((int)$user['id'], (string)$password);
        }

        // Fresh session id on privilege change.
        $_SESSION = [];
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = (string)$user['name'];
        $_SESSION['user_email'] = (string)$user['email'];
        $_SESSION['role'] = (string)$user['role'];

        $this->model->recordLogin((int)$user['id']);

        redirect($this->homeFor((string)$user['role']));
    }

    /* ---------------------------- Buyer sign-up ---------------------------- */

    public function handleBuyerSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=signup_buyer');
        }

        verifyCsrfToken();

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $phone = trim((string)($_POST['phone'] ?? ''));
        $district = trim((string)($_POST['district'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));

        if ($fullName === '' || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}][\p{L}\s.\'\-]{1,119}$/u', $fullName) || !$email) {
            redirect('index.php?page=signup_buyer&error=' . urlencode('Please enter a valid full name (2-120 characters) and email address.'));
        }
        if (!preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $phone))) {
            redirect('index.php?page=signup_buyer&error=' . urlencode('Please enter a valid Sri Lankan phone number.'));
        }
        if (strlen($password) < 8) {
            redirect('index.php?page=signup_buyer&error=' . urlencode('Your password must be at least 8 characters long.'));
        }
        if ($password !== $confirm) {
            redirect('index.php?page=signup_buyer&error=' . urlencode('The two passwords do not match.'));
        }
        if ($phone === '' || $district === '' || $address === '') {
            redirect('index.php?page=signup_buyer&error=' . urlencode('Please complete your phone number, district and street address.'));
        }
        if ($this->model->findUserByEmail($email)) {
            redirect('index.php?page=signup_buyer&error=' . urlencode('That email address is already registered.'));
        }

        // A Buyer account becomes ACTIVE immediately on successful registration.
        $ok = $this->model->registerBuyer(
            $fullName,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $phone,
            $district,
            $address
        );

        redirect($ok
            ? 'index.php?page=login&success=' . urlencode('Your Buyer account is ready. Please sign in.')
            : 'index.php?page=signup_buyer&error=' . urlencode('Your account could not be created. Please try again.'));
    }

    /* --------------------------- Farmer sign-up --------------------------- */

    public function handleFarmerSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=signup_farmer');
        }

        verifyCsrfToken();

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $phone = trim((string)($_POST['phone'] ?? ''));
        $farmAddress = trim((string)($_POST['farm_address'] ?? ''));
        $farmName = trim((string)($_POST['farm_name'] ?? ''));
        $district = trim((string)($_POST['district'] ?? ''));
        $nicNumber = trim((string)($_POST['nic_number'] ?? ''));

        if ($fullName === '' || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}][\p{L}\s.\'\-]{1,119}$/u', $fullName) || !$email) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Please enter a valid full name (2-120 characters) and email address.'));
        }
        if (!preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $phone))) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Please enter a valid Sri Lankan phone number.'));
        }
        if ($nicNumber !== '' && !preg_match('/^(?:\d{9}[vVxX]|\d{12})$/', $nicNumber)) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Please enter a valid NIC number.'));
        }
        if (strlen($password) < 8) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Your password must be at least 8 characters long.'));
        }
        if ($password !== $confirm) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('The two passwords do not match.'));
        }
        if ($phone === '' || $district === '' || $farmAddress === '') {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Please complete your phone number, pickup district and pickup address.'));
        }
        if ($this->model->findUserByEmail($email)) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('That email address is already registered.'));
        }
        // Proof of Farming is required, because the account cannot be approved without it.
        if (empty($_FILES['verification_document']['name'])) {
            redirect('index.php?page=signup_farmer&error=' . urlencode('Please attach your Proof of Farming document.'));
        }

        try {
            $documentPath = handleFileUpload($_FILES['verification_document'], 'assets/documents/verification');
        } catch (Throwable $e) {
            redirect('index.php?page=signup_farmer&error=' . urlencode($e->getMessage()));
        }

        // A Farmer account is created PENDING and cannot use a dashboard until approved.
        $ok = $this->model->registerFarmer(
            $fullName,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $phone,
            $farmName,
            $farmAddress,
            $district,
            $documentPath,
            $nicNumber
        );

        redirect($ok
            ? 'index.php?page=pending_approval&message=' . urlencode('Your Farmer application has been submitted and is now under Admin review.')
            : 'index.php?page=signup_farmer&error=' . urlencode('Your application could not be submitted. Please try again.'));
    }

    /* ------------------------ Courier Partner sign-up ---------------------- */

    public function handleCourierSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=signup_courier');
        }

        verifyCsrfToken();

        // A Courier Partner is an organisation, not an individual driver.
        $organisationName = trim((string)($_POST['company_name'] ?? ''));
        $contactPerson = trim((string)($_POST['contact_person'] ?? ''));
        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $phone = trim((string)($_POST['phone'] ?? ''));
        $officeAddress = trim((string)($_POST['business_address'] ?? ''));
        $officeCity = trim((string)($_POST['office_city'] ?? ''));
        $officePostal = trim((string)($_POST['office_postal'] ?? ''));
        $district = trim((string)($_POST['district'] ?? ''));
        $nicNumber = trim((string)($_POST['nic_number'] ?? ''));

        if ($organisationName === '' || mb_strlen($organisationName) > 160 ||
            $contactPerson === '' || mb_strlen($contactPerson) > 120 || !$email) {
            redirect('index.php?page=signup_courier&error=' . urlencode('Please enter valid organisation/contact details and email address.'));
        }
        if (!preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $phone))) {
            redirect('index.php?page=signup_courier&error=' . urlencode('Please enter a valid Sri Lankan phone number.'));
        }
        if ($nicNumber !== '' && !preg_match('/^(?:\d{9}[vVxX]|\d{12})$/', $nicNumber)) {
            redirect('index.php?page=signup_courier&error=' . urlencode('Please enter a valid NIC number.'));
        }
        if (strlen($password) < 8) {
            redirect('index.php?page=signup_courier&error=' . urlencode('Your password must be at least 8 characters long.'));
        }
        if ($password !== $confirm) {
            redirect('index.php?page=signup_courier&error=' . urlencode('The two passwords do not match.'));
        }
        if ($phone === '' || $officeAddress === '' || $district === '') {
            redirect('index.php?page=signup_courier&error=' . urlencode('Please complete the contact number, office address and primary district.'));
        }
        if ($this->model->findUserByEmail($email)) {
            redirect('index.php?page=signup_courier&error=' . urlencode('That email address is already registered.'));
        }
        // The registration document is required, because the account cannot be approved without it.
        if (empty($_FILES['verification_document']['name'])) {
            redirect('index.php?page=signup_courier&error=' . urlencode('Please attach your Business Registration Document.'));
        }

        try {
            $documentPath = handleFileUpload($_FILES['verification_document'], 'assets/documents/verification');
        } catch (Throwable $e) {
            redirect('index.php?page=signup_courier&error=' . urlencode($e->getMessage()));
        }

        // A Courier Partner account is created PENDING until an Admin approves it.
        $ok = $this->model->registerCourierPartner(
            $organisationName,
            $contactPerson,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $phone,
            $officeAddress,
            $officeCity,
            $officePostal,
            $district,
            $documentPath,
            $nicNumber
        );

        redirect($ok
            ? 'index.php?page=pending_approval&message=' . urlencode('Your Courier Partner organisation registration has been submitted and is now under Admin review.')
            : 'index.php?page=signup_courier&error=' . urlencode('Your registration could not be submitted. Please try again.'));
    }

    /* -------------------------------- Logout ------------------------------- */

    public function handleLogout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrfToken();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        redirect('index.php?page=login&success=' . urlencode('You have been signed out.'));
    }

    /* -------------------------- Password recovery ------------------------- */

    public function handlePasswordResetRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=forgot_password');
        }

        verifyCsrfToken();

        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            redirect('index.php?page=forgot_password&error=' . urlencode('Please enter a valid email address.'));
        }

        $user = $this->model->findUserByEmail($email);
        if (!$user) {
            // Do not reveal whether an account exists.
            redirect('index.php?page=forgot_password&success=' . urlencode(
                'If that email address belongs to a Harvestly account, contact the Harvestly administrator to verify your identity and receive a local password reset link.'
            ));
        }

        /*
         * Harvestly sends no email. A local reset link is generated and stored
         * only when an Admin issues it through scripts/issue_password_reset.php
         * after verifying the account holder's identity, so a reset token is
         * never handed to an anonymous requester.
         */
        redirect('index.php?page=forgot_password&success=' . urlencode(
            'Your account was found. Please contact the Harvestly administrator to verify your identity and receive a local password reset link.'
        ));
    }

    public function handlePasswordReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=forgot_password');
        }

        verifyCsrfToken();

        $token = trim((string)($_POST['token'] ?? ''));
        $password = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($token === '') {
            redirect('index.php?page=forgot_password&error=' . urlencode('That reset link is invalid or has expired.'));
        }
        if (strlen($password) < 8) {
            redirect('index.php?page=reset_password&token=' . urlencode($token) . '&error=' . urlencode('Your new password must be at least 8 characters long.'));
        }
        if ($password !== $confirm) {
            redirect('index.php?page=reset_password&token=' . urlencode($token) . '&error=' . urlencode('The two passwords do not match.'));
        }

        if (!$this->model->resetPassword($token, password_hash($password, PASSWORD_BCRYPT))) {
            redirect('index.php?page=forgot_password&error=' . urlencode('That reset link is invalid or has expired.'));
        }

        redirect('index.php?page=login&success=' . urlencode('Your password has been reset. Please sign in.'));
    }
}
