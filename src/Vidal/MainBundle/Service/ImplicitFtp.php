<?php

namespace Vidal\MainBundle\Service;

class ImplicitFtp
{
    private $server;
    private $port;
    private $username;
    private $password;

    public function __construct($server, $port, $username, $password)
    {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function download($remote, $local = null)
    {
        if ($local === null) {
            $local = tempnam('/tmp', 'implicit_ftp');
        }

        if ($fp = fopen($local, 'w')) {
            $ftp_server = 'ftps://' . $this->server . '/' . $remote;
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $ftp_server);
            curl_setopt($ch, CURLOPT_PORT, $this->port);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
            curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
            curl_setopt($ch, CURLOPT_UPLOAD, 0);
            curl_setopt($ch, CURLOPT_FILE, $fp);

            curl_exec($ch);

            if (curl_error($ch)) {
                curl_close($ch);
                return false;
            }
            else {
                curl_close($ch);
                return $local;
            }
        }
        return false;
    }

    public function upload($local, $remote)
    {
        if ($fp = fopen($local, 'r')) {
            $ftp_server = 'ftps://' . $this->server . '/' . $remote;
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $ftp_server);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
            curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_INFILE, $fp);

            curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            return !$err;
        }
        return false;
    }
}