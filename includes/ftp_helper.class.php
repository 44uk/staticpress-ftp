<?php
require_once(dirname(__FILE__) . '/../vendor/ftp-php/src/Ftp.php');

class ftp_helper {
  private $ftp = null;
  private $status;

  function __construct($host = null, $port = 21, $user = null, $pass = null, $pasv = false) {
    $this->ftp = new Ftp();
    $this->ftp->connect($host, $port);
    $this->status = $this->ftp->login($user, $pass);
    $this->ftp->pasv(!!$pasv);
  }

  public function upload($file_name, $upload_path = null) {
    if (! file_exists($file_name)){ return false; }
    if (! $upload_path){ $upload_path = $file_name; }

    $dir_path = dirname($upload_path);
    $this->ftp->mkDirRecursive($dir_path);

    $this->ftp->put(
      $upload_path,
      $file_name,
      $this->file_type($file_name)
    );
    return $res = false;
  }

  public function set_option($option_array){
    if (! is_array($option_array)){ return false; }
    $this->options = array_merge($this->options, $option_array);
  }

  public function is_authenticated(){
    return $this->status;
  }

  // get file_type
  private function file_type($file_name){
    static $finfo;
    if (! isset($finfo)){ $finfo = new FInfo(FILEINFO_MIME_TYPE); }
    $mime_type = file_exists($file_name) ? $finfo->file($file_name) : false;
    return $mime_type === 'text/plain' ? Ftp::ASCII : Ftp::BINARY;
   }
}
