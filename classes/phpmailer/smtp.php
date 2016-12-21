<?php

class Smtp
{

    const VERSION = '5.2.7';


    const CRLF = "\r\n";


    const DEFAULT_SMTP_PORT = 25;


    const MAX_LINE_LENGTH = 998;


    public $Version = '5.2.7';


    public $SMTP_PORT = 25;

    /
    public $CRLF = "\r\n";


    public $do_debug = 0;


    public $Debugoutput = 'echo';


    public $do_verp = false;


    public $Timeout = 300;


    public $Timelimit = 30;


    protected $smtp_conn;


    protected $error = '';


    protected $helo_rply = '';


    protected $last_reply = '';


    public function __construct()
    {
        $this->smtp_conn = 0;
        $this->error = null;
        $this->helo_rply = null;
        $this->do_debug = 0;
    }


    protected function edebug($str)
    {
        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                )
                . "<br>\n";
                break;
            case 'echo':
            default:
                echo gmdate('Y-m-d H:i:s')."\t".trim($str)."\n";
        }
    }


    public function connect($host, $port = null, $timeout = 30, $options = array())
    {
        // Clear errors to avoid confusion
        $this->error = null;
        // Make sure we are __not__ connected
        if ($this->connected()) {
            // Already connected, generate error
            $this->error = array('error' => 'Already connected to a server');
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_SMTP_PORT;
        }
        // Connect to the SMTP server
        if ($this->do_debug >= 3) {
            $this->edebug('Connection: opening');
        }
        $errno = 0;
        $errstr = '';
        $socket_context = stream_context_create($options);
        //Suppress errors; connection failures are handled at a higher level
        $this->smtp_conn = @stream_socket_client(
            $host . ":" . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );
        // Verify we connected properly
        if (empty($this->smtp_conn)) {
            $this->error = array(
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr
            );
            if ($this->do_debug >= 1) {
                $this->edebug(
                    'SMTP ERROR: ' . $this->error['error']
                    . ": $errstr ($errno)"
                );
            }
            return false;
        }
        if ($this->do_debug >= 3) {
            $this->edebug('Connection: opened');
        }
        // SMTP server can take longer to respond, give longer timeout for first read
        // Windows does not have support for this timeout function
        if (substr(PHP_OS, 0, 3) != 'WIN') {
            $max = ini_get('max_execution_time');
            if ($max != 0 && $timeout > $max) { // Don't bother if unlimited
                @set_time_limit($timeout);
            }
            stream_set_timeout($this->smtp_conn, $timeout, 0);
        }
        // Get any announcement
        $announce = $this->get_lines();
        if ($this->do_debug >= 2) {
            $this->edebug('SERVER -> CLIENT: ' . $announce);
        }
        return true;
    }


    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        // Begin encrypted connection
        if (!stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        )) {
            return false;
        }
        return true;
    }


    public function authenticate(
        $username,
        $password,
        $authtype = 'LOGIN',
        $realm = '',
        $workstation = ''
    ) {
        if (empty($authtype)) {
            $authtype = 'LOGIN';
        }
        switch ($authtype) {
            case 'PLAIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH PLAIN', 334)) {
                    return false;
                }
                // Send encoded username and password
                if (!$this->sendCommand(
                    'User & Password',
                    base64_encode("\0" . $username . "\0" . $password),
                    235
                )
                ) {
                    return false;
                }
                break;
            case 'LOGIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
                    return false;
                }
                if (!$this->sendCommand("Username", base64_encode($username), 334)) {
                    return false;
                }
                if (!$this->sendCommand("Password", base64_encode($password), 235)) {
                    return false;
                }
                break;
            case 'NTLM':

                require_once 'extras/ntlm_sasl_client.php';
                $temp = new stdClass();
                $ntlm_client = new ntlm_sasl_client_class;
                //Check that functions are available
                if (!$ntlm_client->Initialize($temp)) {
                    $this->error = array('error' => $temp->error);
                    if ($this->do_debug >= 1) {
                        $this->edebug(
                            'You need to enable some modules in your php.ini file: '
                            . $this->error['error']
                        );
                    }
                    return false;
                }
                //msg1
                $msg1 = $ntlm_client->TypeMsg1($realm, $workstation); //msg1

                if (!$this->sendCommand(
                    'AUTH NTLM',
                    'AUTH NTLM ' . base64_encode($msg1),
                    334
                )
                ) {
                    return false;
                }
                //Though 0 based, there is a white space after the 3 digit number
                //msg2
                $challenge = substr($this->last_reply, 3);
                $challenge = base64_decode($challenge);
                $ntlm_res = $ntlm_client->NTLMResponse(
                    substr($challenge, 24, 8),
                    $password
                );
                //msg3
                $msg3 = $ntlm_client->TypeMsg3(
                    $ntlm_res,
                    $username,
                    $realm,
                    $workstation
                );
                // send encoded username
                return $this->sendCommand('Username', base64_encode($msg3), 235);
                break;
            case 'CRAM-MD5':
                // Start authentication
                if (!$this->sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', 334)) {
                    return false;
                }
                // Get the challenge
                $challenge = base64_decode(substr($this->last_reply, 4));

                // Build the response
                $response = $username . ' ' . $this->hmac($challenge, $password);

                // send encoded credentials
                return $this->sendCommand('Username', base64_encode($response), 235);
                break;
        }
        return true;
    }


    protected function hmac($data, $key)
    {
        if (function_exists('hash_hmac')) {
            return hash_hmac('md5', $data, $key);
        }



        $bytelen = 64; // byte length for md5
        if (strlen($key) > $bytelen) {
            $key = pack('H*', md5($key));
        }
        $key = str_pad($key, $bytelen, chr(0x00));
        $ipad = str_pad('', $bytelen, chr(0x36));
        $opad = str_pad('', $bytelen, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack('H*', md5($k_ipad . $data)));
    }


    public function connected()
    {
        if (!empty($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                // the socket is valid but we are not connected
                if ($this->do_debug >= 1) {
                    $this->edebug(
                        'SMTP NOTICE: EOF caught while checking if connected'
                    );
                }
                $this->close();
                return false;
            }
            return true; // everything looks good
        }
        return false;
    }


    public function close()
    {
        $this->error = null; // so there is no confusion
        $this->helo_rply = null;
        if (!empty($this->smtp_conn)) {
            // close the connection and cleanup
            fclose($this->smtp_conn);
            if ($this->do_debug >= 3) {
                $this->edebug('Connection: closed');
            }
            $this->smtp_conn = 0;
        }
    }


    public function data($msg_data)
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }


        // Normalize line breaks before exploding
        $lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $msg_data));



        $field = substr($lines[0], 0, strpos($lines[0], ':'));
        $in_headers = false;
        if (!empty($field) && strpos($field, ' ') === false) {
            $in_headers = true;
        }

        foreach ($lines as $line) {
            $lines_out = array();
            if ($in_headers and $line == '') {
                $in_headers = false;
            }
            // ok we need to break this line up into several smaller lines
            //This is a small micro-optimisation: isset($str[$len]) is equivalent to (strlen($str) > $len)
            while (isset($line[self::MAX_LINE_LENGTH])) {
                //Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
                //so as to avoid breaking in the middle of a word
                $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                if (!$pos) { //Deliberately matches both false and 0
                    //No nice break found, add a hard break
                    $pos = self::MAX_LINE_LENGTH - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    //Break at the found point
                    $lines_out[] = substr($line, 0, $pos);
                    //Move along by the amount we dealt with
                    $line = substr($line, $pos + 1);
                }

                if ($in_headers) {
                    $line = "\t" . $line;
                }
            }
            $lines_out[] = $line;

            // Send the lines to the server
            foreach ($lines_out as $line_out) {
                //RFC2821 section 4.5.2
                if (!empty($line_out) and $line_out[0] == '.') {
                    $line_out = '.' . $line_out;
                }
                $this->client_send($line_out . self::CRLF);
            }
        }

        // Message data has been sent, complete the command
        return $this->sendCommand('DATA END', '.', 250);
    }


    public function hello($host = '')
    {
        // Try extended hello first (RFC 2821)
        return (bool)($this->sendHello('EHLO', $host) or $this->sendHello('HELO', $host));
    }


    protected function sendHello($hello, $host)
    {
        $noerror = $this->sendCommand($hello, $hello . ' ' . $host, 250);
        $this->helo_rply = $this->last_reply;
        return $noerror;
    }


    public function mail($from)
    {
        $useVerp = ($this->do_verp ? ' XVERP' : '');
        return $this->sendCommand(
            'MAIL FROM',
            'MAIL FROM:<' . $from . '>' . $useVerp,
            250
        );
    }


    public function quit($close_on_error = true)
    {
        $noerror = $this->sendCommand('QUIT', 'QUIT', 221);
        $err = $this->error; //Save any error
        if ($noerror or $close_on_error) {
            $this->close();
            $this->error = $err; //Restore any error from the quit command
        }
        return $noerror;
    }


    public function recipient($toaddr)
    {
        return $this->sendCommand(
            'RCPT TO',
            'RCPT TO:<' . $toaddr . '>',
            array(250, 251)
        );
    }


    public function reset()
    {
        return $this->sendCommand('RSET', 'RSET', 250);
    }


    protected function sendCommand($command, $commandstring, $expect)
    {
        if (!$this->connected()) {
            $this->error = array(
                'error' => "Called $command without being connected"
            );
            return false;
        }
        $this->client_send($commandstring . self::CRLF);

        $reply = $this->get_lines();
        $code = substr($reply, 0, 3);

        if ($this->do_debug >= 2) {
            $this->edebug('SERVER -> CLIENT: ' . $reply);
        }

        if (!in_array($code, (array)$expect)) {
            $this->last_reply = null;
            $this->error = array(
                'error' => "$command command failed",
                'smtp_code' => $code,
                'detail' => substr($reply, 4)
            );
            if ($this->do_debug >= 1) {
                $this->edebug(
                    'SMTP ERROR: ' . $this->error['error'] . ': ' . $reply
                );
            }
            return false;
        }

        $this->last_reply = $reply;
        $this->error = null;
        return true;
    }


    public function sendAndMail($from)
    {
        return $this->sendCommand('SAML', "SAML FROM:$from", 250);
    }


    public function verify($name)
    {
        return $this->sendCommand('VRFY', "VRFY $name", array(250, 251));
    }


    public function noop()
    {
        return $this->sendCommand('NOOP', 'NOOP', 250);
    }


    public function turn()
    {
        $this->error = array(
            'error' => 'The SMTP TURN command is not implemented'
        );
        if ($this->do_debug >= 1) {
            $this->edebug('SMTP NOTICE: ' . $this->error['error']);
        }
        return false;
    }


    public function client_send($data)
    {
        if ($this->do_debug >= 1) {
            $this->edebug("CLIENT -> SERVER: $data");
        }
        return fwrite($this->smtp_conn, $data);
    }


    public function getError()
    {
        return $this->error;
    }


    public function getLastReply()
    {
        return $this->last_reply;
    }


    protected function get_lines()
    {
        // If the connection is bad, give up straight away
        if (!is_resource($this->smtp_conn)) {
            return '';
        }
        $data = '';
        $endtime = 0;
        stream_set_timeout($this->smtp_conn, $this->Timeout);
        if ($this->Timelimit > 0) {
            $endtime = time() + $this->Timelimit;
        }
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = @fgets($this->smtp_conn, 515);
            if ($this->do_debug >= 4) {
                $this->edebug("SMTP -> get_lines(): \$data was \"$data\"");
                $this->edebug("SMTP -> get_lines(): \$str is \"$str\"");
            }
            $data .= $str;
            if ($this->do_debug >= 4) {
                $this->edebug("SMTP -> get_lines(): \$data is \"$data\"");
            }
            // If 4th character is a space, we are done reading, break the loop, micro-optimisation over strlen
            if ((isset($str[3]) and $str[3] == ' ')) {
                break;
            }
            // Timed-out? Log and break
            $info = stream_get_meta_data($this->smtp_conn);
            if ($info['timed_out']) {
                if ($this->do_debug >= 4) {
                    $this->edebug(
                        'SMTP -> get_lines(): timed-out (' . $this->Timeout . ' sec)'
                    );
                }
                break;
            }
            // Now check if reads took too long
            if ($endtime and time() > $endtime) {
                if ($this->do_debug >= 4) {
                    $this->edebug(
                        'SMTP -> get_lines(): timelimit reached ('.
                        $this->Timelimit . ' sec)'
                    );
                }
                break;
            }
        }
        return $data;
    }


    public function setVerp($enabled = false)
    {
        $this->do_verp = $enabled;
    }


    public function getVerp()
    {
        return $this->do_verp;
    }


    public function setDebugOutput($method = 'echo')
    {
        $this->Debugoutput = $method;
    }


    public function getDebugOutput()
    {
        return $this->Debugoutput;
    }


    public function setDebugLevel($level = 0)
    {
        $this->do_debug = $level;
    }


    public function getDebugLevel()
    {
        return $this->do_debug;
    }


    public function setTimeout($timeout = 0)
    {
        $this->Timeout = $timeout;
    }


    public function getTimeout()
    {
        return $this->Timeout;
    }
}
