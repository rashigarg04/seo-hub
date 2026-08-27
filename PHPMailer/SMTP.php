<?php
namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.8.0';
    const LE = "\r\n";
    const DEFAULT_PORT = 25;
    const MAX_LINE_LENGTH = 998;

    protected $smtp_conn;
    protected $error = [];
    protected $helo_rply;

    public function connect($host, $port = null, $timeout = 30)
    {
        $this->error = [];
        if ($this->connected()) {
            return true;
        }

        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }

        $errno = 0;
        $errstr = '';
        $this->smtp_conn = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!is_resource($this->smtp_conn)) {
            $this->error = [
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr
            ];
            return false;
        }

        return true;
    }

    public function authenticate($username, $password)
    {
        return true;
    }

    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = socket_get_status($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    public function close()
    {
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }
}