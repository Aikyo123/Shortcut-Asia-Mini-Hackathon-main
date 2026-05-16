<?php
// =============================================
// RingitRakyat - Auth Handler
// =============================================
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ----------- REGISTER -----------
    case 'register':
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (!$name || !$email || !$pass) {
            jsonResponse(['success' => false, 'message' => 'All fields are required.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);
        }
        if (strlen($pass) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            jsonResponse(['success' => false, 'message' => 'Email already registered.']);
        }

        $hashed = password_hash($pass, PASSWORD_BCRYPT);
        $colors = ['#FF6B6B','#4ECDC4','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8'];
        $color  = $colors[array_rand($colors)];

        $stmt = $db->prepare("INSERT INTO users (name, email, password, avatar_color) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $hashed, $color);

        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'Account created! Please login.']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Registration failed. Try again.']);
        }
        break;

    // ----------- LOGIN -----------
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (!$email || !$pass) {
            jsonResponse(['success' => false, 'message' => 'Enter email and password.']);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, password, avatar_color, daily_goal FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'No account found with this email.']);
        }

        $user = $result->fetch_assoc();
        if (!password_verify($pass, $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Wrong password. Try again.']);
        }

        $_SESSION['user_id']      = $user['id'];
        $_SESSION['user_name']    = $user['name'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['avatar_color'] = $user['avatar_color'];
        $_SESSION['daily_goal']   = $user['daily_goal'];

        jsonResponse(['success' => true, 'message' => 'Login successful!', 'name' => $user['name']]);
        break;

    // ----------- LOGOUT -----------
    case 'logout':
        session_destroy();
        jsonResponse(['success' => true]);
        break;

    // ----------- FORGOT PASSWORD (send OTP) -----------
    case 'send_otp':
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email.']);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'No account with this email.']);
        }

        // Generate 6-digit OTP
        $otp     = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate old OTPs
        $db->prepare("UPDATE otp_tokens SET used=1 WHERE email=?")->execute() ;
        $stmt = $db->prepare("DELETE FROM otp_tokens WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $stmt = $db->prepare("INSERT INTO otp_tokens (email, otp_code, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $email, $otp, $expires);
        $stmt->execute();

        // In real app: send via email (PHPMailer). For demo, we return it.
        // REMOVE this in production and use real email sending!
        jsonResponse([
            'success' => true,
            'message' => 'OTP sent! (Demo: check below)',
            'demo_otp' => $otp   // REMOVE IN PRODUCTION
        ]);
        break;

    // ----------- VERIFY OTP -----------
    case 'verify_otp':
        $email = trim($_POST['email'] ?? '');
        $otp   = trim($_POST['otp'] ?? '');

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM otp_tokens WHERE email=? AND otp_code=? AND used=0 AND expires_at > NOW()");
        $stmt->bind_param('ss', $email, $otp);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid or expired OTP.']);
        }

        $_SESSION['otp_verified_email'] = $email;
        jsonResponse(['success' => true, 'message' => 'OTP verified!']);
        break;

    // ----------- RESET PASSWORD -----------
    case 'reset_password':
        $email   = $_SESSION['otp_verified_email'] ?? '';
        $newpass = $_POST['password'] ?? '';

        if (!$email) {
            jsonResponse(['success' => false, 'message' => 'Session expired. Start over.']);
        }
        if (strlen($newpass) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        }

        $db     = getDB();
        $hashed = password_hash($newpass, PASSWORD_BCRYPT);
        $stmt   = $db->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param('ss', $hashed, $email);
        $stmt->execute();

        // Mark OTP as used
        $stmt = $db->prepare("UPDATE otp_tokens SET used=1 WHERE email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        unset($_SESSION['otp_verified_email']);
        jsonResponse(['success' => true, 'message' => 'Password reset! Please login.']);
        break;

    // ----------- CHECK SESSION -----------
    case 'check':
        if (isset($_SESSION['user_id'])) {
            jsonResponse([
                'logged_in'    => true,
                'name'         => $_SESSION['user_name'],
                'email'        => $_SESSION['user_email'],
                'avatar_color' => $_SESSION['avatar_color'],
                'daily_goal'   => $_SESSION['daily_goal']
            ]);
        } else {
            jsonResponse(['logged_in' => false]);
        }
        break;

    default:
        jsonResponse(['error' => 'Unknown action'], 400);
}
?>
