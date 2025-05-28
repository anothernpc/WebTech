<?php
declare (strict_types = 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/UserEntity.php';
require_once __DIR__ . '/../TemplateEngine.php';

class MailService {
    private string $senderMail;
    private string $senderPassword;
    private PHPMailer $mail;
    private TemplateEngine $templateEngine;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $this->senderMail = $env['MAIL_NAME'];
        $this->senderPassword = $env['MAIL_PASSWORD'];
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = "UTF-8";
        $this->templateEngine = new TemplateEngine();
    }

    public function sendEmail(string $destEmail, string $subject, string $message): bool {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->senderMail;
            $this->mail->Password = $this->senderPassword;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port = 465;
            $this->mail->setFrom($this->senderMail, 'TicketMaster');
            $this->mail->addAddress($destEmail);
            $this->mail->isHTML();
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->send();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function sendLoginMail(UserEntity $user): bool
    {
        $message = $this->templateEngine->render(
            __DIR__ . '/../templates/login.html',
            [
                'time' => date('H:i:s Y-m-d', $_SERVER['REQUEST_TIME']),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'device' => $_SERVER['HTTP_USER_AGENT']
            ]
        );

        return $this->sendEmail(
            $user->getMail(),
            'Новый вход в ваш аккаунт',
            $message
        );
    }

    public function sendVerificationMessage(UserEntity $user): bool
    {
        $verificationLink = "https://localhost/Webtech/auth/continueVerification?user_id=" . $user->getId();

        $message = $this->templateEngine->render(
            __DIR__ . '/../templates/verification.html',
            [
                'username' => $user->getUsername(),
                'link' => $verificationLink
            ]
        );

        return $this->sendEmail(
            $user->getMail(),
            'Подтверждение электронной почты',
            $message
        );
    }
}
