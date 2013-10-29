<?php

namespace Flare;

use Flare\Mail\PHPMailerException;
use Flare\Mail\PHPMailer;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Mail
{
    /**
     * 
     * @var string
     */
    const DEFAULT_SMTP_SECURITY = 'ssl';

    /**
     * 
     * @var int
     */
    const DEFAULT_SMTP_PORT = 25;

    /**
     * 
     * @var \Flare\Mail\PHPMailer
     */
    private $mail;

    /**
     * 
     * @var array|boolean
     */
    private static $smtpConfig = array();

    /**
     * 
     * @var \Flare\Mail\PHPMailerException
     */
    private $error;

    public function __construct()
    {
        $this->mail = new PHPMailer();
        if (self::$smtpConfig !== false) {
            if (!self::$smtpConfig) {
                self::smtp(F::$config->get('mail'));
            }
            if (!empty(self::$smtpConfig['host'])) {
                $this->setSmtp(
                    self::$smtpConfig['host'],
                    self::$smtpConfig['port'],
                    isset(self::$smtpConfig['username']) ? self::$smtpConfig['username'] : '',
                    isset(self::$smtpConfig['password']) ? self::$smtpConfig['password'] : '',
                    self::$smtpConfig['security']
                );
            }
        }
    }

    /**
     * 
     * @param array|boolean $config
     * @return void
     */
    public static function smtp($config)
    {
        if ($config === false) {
            self::$smtpConfig = false;
            return;
        }
        $default = array(
            'port' => self::DEFAULT_SMTP_PORT,
            'security' => self::DEFAULT_SMTP_SECURITY
        );
        self::$smtpConfig = array_merge($default, (array) $config);
    }

    /**
     * 
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $security
     * @return \Flare\Mail
     */
    public function setSmtp($host, $port = self::DEFAULT_SMTP_PORT, $username = '', $password = '', $security = self::DEFAULT_SMTP_SECURITY)
    {
        $this->mail->isSMTP();
        $this->mail->Host = implode(';', (array) $host);
        $this->mail->Port = $port;
        if ($username) {
            $this->mail->Username = $username;
            $this->mail->Password = $password;
            $this->mail->SMTPAuth = true;
        }
        $this->mail->SMTPSecure = $security;
        return $this;
    }

    /**
     * 
     * @param array $emails
     * @return \Flare\Mail
     */
    public function setCc(array $emails)
    {
        foreach ($emails as $email => $name) {
            $this->addCc($email, $name);
        }
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return \Flare\Mail
     */
    public function addCc($email, $name = '')
    {
        try {
            $this->mail->addCc($email, $name);
        } catch (PHPMailerException $ex) {
            $this->error = $ex;
        }
        return $this;
    }

    /**
     * 
     * @param array $emails
     * @return \Flare\Mail
     */
    public function setBcc(array $emails)
    {
        foreach ($emails as $email => $name) {
            $this->addBcc($email, $name);
        }
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return \Flare\Mail
     */
    public function addBcc($email, $name = '')
    {
        try {
            $this->mail->addBcc($email, $name);
        } catch (PHPMailerException $ex) {
            $this->error = $ex;
        }
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function error()
    {
        if ($this->error) {
            return $this->error->getMessage();
        }
        return $this->mail->ErrorInfo;
    }

    /**
     * 
     * @param string $subject
     * @return \Flare\Mail
     */
    public function setSubject($subject)
    {
        $this->mail->Subject = (string) $subject;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->mail->Subject;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return \Flare\Mail
     */
    public function setFrom($email, $name = '')
    {
        try {
            $this->mail->setFrom($email, $name);
        } catch (PHPMailerException $ex) {
            $this->error = $ex;
        }
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getFrom()
    {
        return $this->mail->From;
    }

    /**
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->mail->Body;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return \Flare\Mail
     */
    public function addTo($email, $name = '')
    {
        try {
            $this->mail->addAddress($email, $name);
        } catch (PHPMailerException $ex) {
            $this->error = $ex;
        }
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return \Flare\Mail
     */
    public function addReplyTo($email, $name = '')
    {
        try {
            $this->mail->addReplyTo($email, $name);
        } catch (PHPMailerException $ex) {
            $this->error = $ex;
        }
        return $this;
    }

    /**
     * 
     * @param array $emails
     * @return \Flare\Mail
     */
    public function setReplyTo(array $emails)
    {
        foreach ($emails as $email => $name) {
            $this->addReplyTo($email, $name);
        }
        return $this;
    }

    /**
     * 
     * @param array $emails
     * @return \Flare\Mail
     */
    public function setTo(array $emails)
    {
        foreach ($emails as $email => $name) {
            $this->addTo($email, $name);
        }
        return $this;
    }

    /**
     * 
     * @param string $location
     * @param string $filename
     * @return \Flare\Mail
     */
    public function attach($location, $filename = '')
    {
        $this->mail->addAttachment($location, $filename);
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function send()
    {
        if ($this->error) return false;
        return $this->mail->send();
    }

    /**
     * 
     * @param string $message
     * @param boolean $isHtml
     * @return \Flare\Mail
     */
    public function setMessage($message, $isHtml = false)
    {
        $message = (string) $message;
        if (!$isHtml) {
            $this->mail->Body = $message;
        } else {
            $this->mail->msgHTML($message, F::$uri->baseUrl);
        }
        return $this;
    }

    /**
     * 
     * @param string $message
     * @return \Flare\Mail
     */
    public function setAltMessage($message)
    {
        $this->mail->AltBody = (string) $message;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getAltMessage()
    {
        return $this->mail->AltBody;
    }
}