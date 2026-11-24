<?php
require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmailRecuperacao(string $emailDestino, string $link): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($emailDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperação de Senha - BicoNline';

        $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #1d1f2b; max-width: 520px;'>
                <h2>Redefinição de senha</h2>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>BicoNline</strong>.</p>
                <p>Clique no botão abaixo para criar uma nova senha (o link expira em 1 hora):</p>
                <p style='margin: 24px 0;'>
                    <a href='{$linkSeguro}' style='background: #6c5ce7; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                        Redefinir minha senha
                    </a>
                </p>
                <p style='font-size: 13px; color: #666;'>Se você não solicitou essa alteração, ignore este e-mail.</p>
            </div>
        ";
        $mail->AltBody = "Para redefinir sua senha no BicoNline, acesse o link (válido por 1 hora): {$link}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}