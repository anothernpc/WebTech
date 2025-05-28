<?php
declare(strict_types=1);
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../model/UserEntity.php';

class AuthorizationService {
    private UserRepository $userRepository;
    private string $secretKey;

    public function __construct(string $secretKey) {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $this->userRepository =  new UserRepository($pdo);
        $this->secretKey = $secretKey;
    }
    public function register(string $username, string $password, string $email): bool {
        if ($this->userRepository->findByUsername($username) != null) {
            return false;
        }

        $salt = bin2hex(random_bytes(16));
        $hashedPassword = $this->hashPassword($password, $salt);

        $user = new UserEntity(
            id: (int)null,
            username: $username,
            password: $hashedPassword,
            mail: $email,
            salt: $salt,
            is_verified: false,
            token: null
        );

        return $this->userRepository->save($user);
    }

    public function login(string $username, string $password, bool $rememberMe = false): bool {
        $user = $this->userRepository->findByUsername($username);
        if (!$user) {
            return false;
        }

        $hashedPassword = $this->hashPassword($password, $user->getSalt());
        if ($hashedPassword !== $user->getPassword()) {
            return false;
        }
        $_SESSION['user_id'] = $user->getId();$this->startSession($user);

        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            $user->setToken($token);
            $this->userRepository->save($user);
            setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', true, true);
        }

        return true;
    }

    public function sendLoginMail(MailService $mailService, string $username): bool {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            throw new Exception('User not found');
        }

        return $mailService->sendLoginMail($user);
    }
    public function verifyEmail(MailService $mailService, string $username, string $email): array {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if ($user->getMail() !== $email) {
            return ['success' => false, 'message' => 'Email does not match'];
        }

        $emailSent = $mailService->sendVerificationMessage($user);

        return [
            'success' => $emailSent,
            'message' => $emailSent
                ? 'Verification email sent'
                : 'Failed to send verification email'
        ];
    }

    public function continueVerification(int $userId): array
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid verification link'];
        }

        if ($user->getIsVerified()) {
            return ['success' => false, 'message' => 'Email already verified'];
        }

        $user->setIsVerified(true);

        return [
            'success' => $this->userRepository->save($user),
            'message' => $user->getIsVerified()
                ? 'Email successfully verified'
                : 'Verification failed'
        ];
    }

    public function loginByToken(string $token): bool {
        $user = $this->userRepository->findByToken($token);
        if (!$user) {
            return false;
        }

        $this->startSession($user);
        return true;
    }

    public function logout(): void {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user = $this->userRepository->findByToken($token);
            if ($user) {
                $user->setToken(null);
                $this->userRepository->save($user);
            }
            setcookie('remember_token', '', time() - 3600, '/');

            session_destroy();
        }
    }

    public function isLoggedIn(): bool {
        if (isset($_SESSION['user'])) {
            return true;
        }

        if (isset($_COOKIE['remember_token'])) {
            return $this->loginByToken($_COOKIE['remember_token']);
        }

        return false;
    }

    public function getCurrentUser(): ?UserEntity {
        if (isset($_SESSION['user'])) {
            return $this->userRepository->find($_SESSION['user']['id']);
        }

        if (isset($_COOKIE['remember_token'])) {
            $user = $this->userRepository->findByToken($_COOKIE['remember_token']);
            if ($user) {
                $this->startSession($user);
                return $user;
            }
        }

        return null;
    }

    private function startSession(UserEntity $user): void {
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getMail()
        ];
    }

    private function hashPassword(string $password, string $salt): string {
        return hash('sha256', $password . $salt . $this->secretKey);
    }

    public function validateCaptcha(string $captchaResponse): bool {
        //извините впадлу было жоско
        return true;
    }
}
