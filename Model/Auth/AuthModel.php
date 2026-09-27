<?php
/**
 * Harvestly authentication model.
 * MySQLi prepared statements only. No PDO, no external auth service.
 */
require_once __DIR__ . '/../../config/app.php';

class AuthModel
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Look up a user for login.
     *
     * `role` is normalised to admin | buyer | farmer | courier.
     * `status` is normalised to:
     *   approved  - the account can use its dashboard
     *   pending   - Farmer / Courier Partner awaiting Admin approval
     *   otherwise - the raw account_status (rejected / suspended)
     */
    public function findUserByEmail($email)
    {
        $user = db_fetch_one(
            'SELECT user_id AS id, full_name AS name, email, password_hash,
                    LOWER(role) AS role, LOWER(account_status) AS status
             FROM users WHERE email = ? LIMIT 1',
            's',
            [$email]
        );
        if (!$user) return false;

        if ($user['role'] === 'courier_partner') $user['role'] = 'courier';

        if ($user['status'] === 'active') {
            $verification = 'APPROVED';
            if ($user['role'] === 'farmer') {
                $verification = (string)db_scalar(
                    'SELECT verification_status FROM farmer_profiles WHERE farmer_id = ?',
                    'i',
                    [$user['id']],
                    'PENDING'
                );
            } elseif ($user['role'] === 'courier') {
                $verification = (string)db_scalar(
                    'SELECT verification_status FROM courier_partner_profiles WHERE courier_partner_id = ?',
                    'i',
                    [$user['id']],
                    'PENDING'
                );
            }
            $user['status'] = $verification === 'APPROVED' ? 'approved' : 'pending';
        }

        return $user;
    }

    /**
     * Shared registration path.
     * A Buyer is created ACTIVE; Farmer and Courier Partner are created PENDING.
     */
    private function register(
        string $role,
        string $name,
        string $email,
        string $hash,
        string $phone,
        string $district,
        callable $profile,
        ?string $document = null
    ): bool {
        $phone = preg_replace('/[\s()\-]+/', '', $phone) ?? '';
        $districtId = (int)db_scalar(
            'SELECT district_id FROM districts WHERE district_name = ? AND is_active = 1',
            's',
            [$district],
            0
        );
        if (
            $districtId <= 0
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || trim($name) === ''
            || !preg_match('/^(?:0\d{9}|\+94\d{9})$/', $phone)
        ) {
            return false;
        }

        $this->db->begin_transaction();
        try {
            if (!db_execute(
                'INSERT INTO users (role, full_name, email, password_hash, phone, account_status)
                 VALUES (?, ?, ?, ?, ?, ?)',
                'ssssss',
                [$role, $name, $email, $hash, $phone, $role === 'BUYER' ? 'ACTIVE' : 'PENDING']
            )) {
                throw new RuntimeException('Registration failed.');
            }
            $id = (int)$this->db->insert_id;

            if (!$profile($id, $districtId)) {
                throw new RuntimeException('Profile could not be created.');
            }

            if ($document && !db_execute(
                "INSERT INTO verification_documents
                 (user_id, document_type, original_file_name, stored_file_path, status)
                 VALUES (?, 'Supporting Verification Document', ?, ?, 'PENDING')",
                'iss',
                [$id, basename($document), $document]
            )) {
                throw new RuntimeException('Verification document could not be saved.');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function registerBuyer($name, $email, $hash, $phone, $district, $address)
    {
        return $this->register(
            'BUYER',
            $name,
            $email,
            $hash,
            $phone,
            $district,
            static fn($id, $districtId): bool => db_execute(
                'INSERT INTO buyer_profiles (buyer_id, default_address_line1, default_district_id) VALUES (?, ?, ?)',
                'isi',
                [$id, $address, $districtId]
            )
        );
    }

    public function registerFarmer($name, $email, $hash, $phone, $farmName, $address, $district, $document, $nicNumber = '')
    {
        $nic = self::normaliseNic($nicNumber);
        return $this->register(
            'FARMER',
            $name,
            $email,
            $hash,
            $phone,
            $district,
            static fn($id, $districtId): bool => db_execute(
                "INSERT INTO farmer_profiles
                 (farmer_id, farm_name, nic_number, pickup_address_line1, district_id, verification_status)
                 VALUES (?, ?, ?, ?, ?, 'PENDING')",
                'isssi',
                [$id, $farmName !== '' ? $farmName : null, $nic, $address, $districtId]
            ),
            $document
        );
    }

    /** A Courier Partner is an organisation, not an individual driver. */
    public function registerCourierPartner($organisationName, $contactPerson, $email, $hash, $phone, $address, $city, $postal, $district, $document, $nicNumber = '')
    {
        $nic = self::normaliseNic($nicNumber);
        return $this->register(
            'COURIER_PARTNER',
            $organisationName,
            $email,
            $hash,
            $phone,
            $district,
            static fn($id, $districtId): bool => db_execute(
                "INSERT INTO courier_partner_profiles
                 (courier_partner_id, organisation_name, contact_person_name, nic_number,
                  office_address_line1, office_city_town, office_postal_code,
                  office_district_id, availability_status, verification_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'UNAVAILABLE', 'PENDING')",
                'issssssi',
                [$id, $organisationName, $contactPerson !== '' ? $contactPerson : null, $nic,
                 $address, $city ?: null, $postal ?: null, $districtId]
            ),
            $document
        );
    }

    /**
     * Tidy an optional NIC number: spaces and hyphens removed, upper case, and
     * capped to the column width. Returns null when nothing usable was entered.
     */
    private static function normaliseNic($value): ?string
    {
        $nic = strtoupper(preg_replace('/[\s\-]+/', '', trim((string)$value)));
        if ($nic === '') {
            return null;
        }
        return substr($nic, 0, 20);
    }

    /** Store the successful sign-in time for the audit trail. */
    public function recordLogin(int $userId): bool
    {
        return db_execute('UPDATE users SET last_login_at = NOW() WHERE user_id = ?', 'i', [$userId]);
    }

    public function updatePassword(int $userId, string $plainPassword): bool
    {
        return db_execute(
            'UPDATE users SET password_hash = ? WHERE user_id = ?',
            'si',
            [password_hash($plainPassword, PASSWORD_BCRYPT), $userId]
        );
    }

    /**
     * Create a local password reset token.
     *
     * Harvestly sends no email. This is used by
     * scripts/issue_password_reset.php after an Admin has verified the account
     * holder's identity, so a token is never issued to an anonymous requester.
     */
    public function createPasswordResetToken($email)
    {
        $user = $this->findUserByEmail($email);
        if (!$user) return null;
        $token = bin2hex(random_bytes(32));
        db_execute(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL',
            'i',
            [$user['id']]
        );
        return db_execute(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
            'is',
            [$user['id'], hash('sha256', $token)]
        ) ? $token : null;
    }

    /** Single-use, one-hour reset token. */
    public function resetPassword($token, $hash)
    {
        $this->db->begin_transaction();
        try {
            $row = db_fetch_one(
                'SELECT reset_id, user_id FROM password_reset_tokens
                 WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
                 FOR UPDATE',
                's',
                [hash('sha256', $token)]
            );
            if (!$row) {
                $this->db->rollback();
                return false;
            }
            if (
                !db_execute('UPDATE users SET password_hash = ? WHERE user_id = ?', 'si', [$hash, $row['user_id']])
                || !db_execute(
                    'UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL',
                    'i',
                    [$row['user_id']]
                )
            ) {
                throw new RuntimeException('Password could not be reset.');
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }
}
