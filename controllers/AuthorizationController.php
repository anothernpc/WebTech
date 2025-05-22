<?php
require_once __DIR__ . '/../services/AuthorizationService.php';

class AuthorizationController {
    private AuthorizationService $authService;

    public function __construct() {
        $this->authService = new AuthorizationService( 'YOUR_SECRET_KEY');
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';

            if ($this->authService->register($username, $password, $email)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
            }
        }
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            $captcha = $_POST['captcha'] ?? '';

            if (!$this->authService->validateCaptcha($captcha)) {
                echo json_encode(['success' => false, 'message' => 'Invalid CAPTCHA']);
                return;
            }

            if ($this->authService->login($username, $password, $rememberMe)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        }
    }

    public function logout(): void {
        $this->authService->logout();
        header('Location: /login');
        exit();
    }

    public function checkAuth(): void {
        if ($this->authService->isLoggedIn()) {
            echo json_encode(['authenticated' => true]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
    }
}