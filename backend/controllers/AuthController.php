<?php
// backend/controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

class AuthController {
    private User $user;

    public function __construct() {
        $this->user = new User();
    }

    /** GET /login */
    public function loginForm() {

        // 1. Check if the user session is already active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            // Read user role to determine the correct landing page
            $userRole = strtolower($_SESSION['user']['role'] ?? 'customer');
            
            if ($userRole === 'admin') {
                redirect(APP_URL . '/admin/dashboard');
            } else {
                // If a specific redirect target is provided via URL parameters, use it
                $redirectTarget = $_GET['redirect'] ?? APP_URL . '/account/orders';
                redirect($redirectTarget);
            }
        }

        // 2. Clear browser cache to handle back-button operations safely
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        
        $error    = flash('auth_error');
        $redirect = $_GET['redirect'] ?? APP_URL . '/account';
        require __DIR__ . '/../../frontend/views/pages/login.php';
    }

    /** POST /login */
    public function login() {
        verifyCsrf();
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            flash('auth_error', 'Email and password are required.');
            redirect(APP_URL . '/login');
        }

        $user = $this->user->findByEmail($email);
        
        if (!$user || !$this->user->verifyPassword($password, $user['password'])) {
            flash('auth_error', 'Invalid email or password.');
            redirect(APP_URL . '/login');
        }

        // if (!$user) {
        //     die('<pre style="background:#1a1a1a;color:#f87171;padding:2rem;font-size:1rem">'
        //         . "❌ No user found for email: " . htmlspecialchars($email)
        //         . "\n\nCheck: is the email exactly right in the DB?"
        //         . '</pre>');
        // }

        // $passwordOk = password_verify($password, $user['password'] ?? '');
        // if (!$passwordOk) {
        //     die('<pre style="background:#1a1a1a;color:#f87171;padding:2rem;font-size:1rem">'
        //         . "❌ Password mismatch for: " . htmlspecialchars($email)
        //         . "\n\nDB row returned:\n"
        //         . print_r(array_merge($user, ['password' => '(hidden)']), true)
        //         . "\n\npassword column value exists: " . (!empty($user['password']) ? 'YES' : 'NO — column is empty or named differently!')
        //         . "\n\nAll column keys in DB row:\n"
        //         . implode(', ', array_keys($user))
        //         . '</pre>');
        // }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        // ── STEP 7: DEBUG — confirm session was written ──────
        // die('<pre style="background:#1a1a1a;color:#4ade80;padding:2rem;font-size:1rem">'
        //     . "✅ Session written:\n" . print_r($_SESSION['user'], true)
        //     . "\nRedirecting to: "
        //     . (strtolower($user['role']) === 'admin' ? APP_URL.'/admin/dashboard' : APP_URL.'/account/orders')
        //     . '</pre>');

        error_log('ADMIN LOGIN: role = ' . ($_SESSION['user']['role'] ?? 'none'));
        error_log('Redirect to: ' . ($_SESSION['user']['role'] === 'admin' ? APP_URL . '/admin/dashboard' : APP_URL . '/shop'));

        if (strtolower($user['role']) === 'admin') {
            redirect(APP_URL . '/admin/dashboard');
        } else {
            redirect($_POST['redirect'] ?? APP_URL . '/account/orders');
        }

        // Merge guest cart into session
        // redirect($_POST['redirect'] ?? APP_URL . '/account');

        // Choose redirect target based on role
        // $redirectTo = ($_SESSION['user']['role'] === 'admin')
        // ? APP_URL . '/admin/dashboard'
        // : ($_POST['redirect'] ?? APP_URL . '/shop');

        // redirect($redirectTo);
    }

    /** GET /register */
    public function registerForm() {
        $error = flash('reg_error');
        require __DIR__ . '/../../frontend/views/pages/register.php';
    }

    /** POST /register */
    public function register() {
        verifyCsrf();
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');

        if (!$name || !$email || !$password) {
            flash('reg_error', 'All fields are required.');
            redirect(APP_URL . '/register');
        }
        if ($password !== $confirm) {
            flash('reg_error', 'Passwords do not match.');
            redirect(APP_URL . '/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('reg_error', 'Invalid email address.');
            redirect(APP_URL . '/register');
        }
        if ($this->user->findByEmail($email)) {
            flash('reg_error', 'An account with this email already exists.');
            redirect(APP_URL . '/register');
        }

        $id = $this->user->create(compact('name', 'email', 'phone', 'password'));
        $_SESSION['user'] = [
            'id'    => $id,
            'name'  => $name,
            'email' => $email,
            'role'  => 'customer',
        ];
        flash('success', 'Welcome to GroceryDash, ' . $name . '!');
        redirect(APP_URL . '/account/orders');
    }

    /** GET /logout */
    public function logout() {
        // Destroy session
        $_SESSION = [];
        session_destroy();

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Regenerate session ID to be safe
        session_start();
        session_regenerate_id(true);
        session_destroy();

        // Redirect to home page (or login page)
        header('Location: ' . APP_URL);
        exit;
    }
}
