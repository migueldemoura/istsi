<?php
declare(strict_types = 1);

namespace ISTSI\Services;

use ISTSI\Exception\Exception;

class Mailer
{
    private $fromEmail;
    private $fromName;
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct($host, $port, $username, $password, $fromName, $fromEmail)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function sendMail($destination, $subject, $body)
    {
        $mail = new \PHPMailer();

        $mail->isSMTP();
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = $this->username;
        $mail->Password = $this->password;

        $mail->setFrom($this->fromEmail, $this->fromName);
        $mail->addAddress($destination);
        $mail->Subject = $subject;
        $mail->msgHTML($body);

        if (!$mail->send()) {
            throw new Exception($mail->ErrorInfo);
        }

        return true;
    }
}
