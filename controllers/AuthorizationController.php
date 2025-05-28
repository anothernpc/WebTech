<?php
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/MailService.php';

class AuthorizationController {
    private AuthorizationService $authService;
    private MailService $mailService;

    public function __construct() {
        $this->authService = new AuthorizationService( 'YOUR_SECRET_KEY');
        $this->mailService = new MailService();
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
            exit;
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

    public function verifyEmail(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';

        try {
            $result = $this->authService->verifyEmail($this->mailService, $username, $email);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error sending verification: ' . $e->getMessage()
            ]);
        }
    }

    public function continueVerification(): void
    {
        $userId = (int)($_GET['user_id'] ?? 0);
        $result = $this->authService->continueVerification($userId);

        if ($result['success']) {
            header('Location: /verification-success');
        } else {
            header('Location: /verification-error?message=' . urlencode($result['message']));
        }
        exit;
    }

    public function sendLoginMail(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';

        try {
            $success = $this->authService->sendLoginMail($this->mailService, $username);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}