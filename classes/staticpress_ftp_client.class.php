<?php
class StaticPress_FTPClient {
    private $host;
    private $user;
    private $pass;
    private $dir  = '/';
    private $port = 21;
    private $timeout = 30;
    private $pasv = false;
    private $ssl  = false;
    private $resource;
    private $connected = false;

    function __construct($host, $user, $pass, $options = array()) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        if (isset($options['dir'])) {
            $this->dir = $options['dir'];
        }
        if (isset($options['port'])) {
            $this->port = (int)$options['port'];
        }
        if (isset($options['timeout'])) {
            $this->timeout = (int)$options['timeout'];
        }
        if (isset($options['pasv'])) {
            $this->pasv = !!$options['pasv'];
        }
        if (isset($options['ssl'])) {
            $this->ssl = !!$options['ssl'];
        }
    }

    function __destruct() {
        $this->disconnect();
    }

    public function upload($local_path, $remote_path = null, $mode = FTP_BINARY) {
        if (! $this->isConnected()) { $this->connect(); }
        if (! file_exists($local_path)){ return false; }
        if (! $remote_path){ $remote_path = $local_path; }
        $mode = $this->fileType($local_path);
        $dir_path = dirname($remote_path);
        $this->mkDirR($dir_path);
        $result = ftp_put($this->connection(), $remote_path, $local_path, $mode);
        return $result;
    }

    public function mkdirR($dir_path) {
        $parts = explode(DIRECTORY_SEPARATOR, $dir_path);
        $resource = $this->connection();
        $pwd = ftp_pwd($resource);
        foreach($parts as $part){
            ftp_mkdir($resource, $part);
            ftp_chdir($resource, $part);
        }
        return ftp_chdir($resource, $pwd);
    }

    public function pwd() {
        return ftp_pwd($this->connection());
    }

    public function nlist($path = '.') {
        return ftp_nlist($this->connection(), $path);
    }

    public function connect() {
        if ($this->isConnected()) { return $this; }
        $this->connected = ftp_login($this->connection(), $this->user, $this->pass);
        if ($this->isConnected()) {
            ftp_pasv($this->resource, $this->pasv);
            ftp_chdir($this->resource, $this->dir);
        }
        return $this;
    }

    public function disconnect() {
        if ($this->isConnected()) {
            $this->connected = ! ftp_close($this->connection());
            $this->resource = null;
        }
        return $this;
    }

    public function isConnected() {
        return $this->connected;
    }

    public function isSecure() {
        return $this->ssl;
    }

    private function connection() {
        if (! $this->resource) {
            $fn_connect = $this->isSecure() ? 'ftp_ssl_connect' : 'ftp_connect';
            $this->resource = $fn_connect($this->host, $this->port, $this->timeout);
        }
        return $this->resource;
    }

    private function fileType($file_name) {
        static $finfo;
        if (! isset($finfo)){ $finfo = new FInfo(FILEINFO_MIME_TYPE); }
        $mime_type = file_exists($file_name) ? $finfo->file($file_name) : false;
        return $mime_type === 'text/plain' ? FTP_ASCII : FTP_BINARY;
    }
}
