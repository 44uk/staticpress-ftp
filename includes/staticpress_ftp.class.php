<?php
class staticpress_ftp {
  static $debug_mode = false;
  static $instance;

  private $options = array();
  private $ftp;

  static public function plugin_basename() {
    return plugin_basename(dirname(dirname(__FILE__)).'/plugin.php');
  }

  function __construct($options = array()){
    self::$instance = $this;
    $this->options = $options;
    add_action('StaticPress::file_put', array($this, 'file_put'), 10, 2);
  }

  public function file_put($file_dest, $url){
    $this->ftp_upload($file_dest);
  }

  private function ftp_upload($file_name){
    if (! file_exists($file_name)){ return false; }
    $upload_path = str_replace(static_press_admin::static_dir(), '', $file_name);

    $result = false;
    if ($ftp = $this->ftp()) {
      $result = $ftp->upload($file_name, $upload_path);

      if (self::$debug_mode && function_exists('dbgx_trace_var')) {
        dbgx_trace_var($result);
      }
    }
    return $result;
  }

  private function ftp(){
    if (isset($this->ftp)) { return $ftp; }

    $this->ftp = new ftp_helper(
      isset($this->options['host']) ? $this->options['host'] : null,
      isset($this->options['port']) ? $this->options['port'] : null,
      isset($this->options['user']) ? $this->options['user'] : null,
      isset($this->options['pass']) ? $this->options['pass'] : null,
      isset($this->options['pasv']) ? $this->options['pasv'] : null
    );

    return $this->ftp->is_authenticated() ? $this->ftp : false;
  }
}
