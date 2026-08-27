<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority = null;
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/html';
    public $Encoding = '8bit';
    public $From = 'root@localhost';
    public $FromName = 'Root User';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $WordWrap = 0;
    public $Mailer = 'smtp';
    public $Sendmail = '/usr/sbin/sendmail';
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = '';
    public $SMTPSecure = '';
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $Timeout = 30;
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';

    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $smtp = null;

    public function __construct($exceptions = null)
    {
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = [$address, $name];
        return true;
    }

    public function isHTML($ishtml = true)
    {
        if ($ishtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    public function send()
    {
        try {
            if ($this->Mailer === 'smtp') {
                $this->smtp = new SMTP();
                if (!$this->smtp->connect($this->Host, $this->Port, $this->Timeout)) {
                    throw new Exception('SMTP Connect failed.');
                }
                if ($this->SMTPAuth) {
                    if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                        throw new Exception('SMTP Auth failed.');
                    }
                }
            }
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: {$this->ContentType}; charset={$this->CharSet}\r\n";
            $headers .= "From: {$this->FromName} <{$this->From}>\r\n";
            
            foreach ($this->to as $recipient) {
                mail($recipient[0], $this->Subject, $this->Body, $headers);
            }
            return true;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}