<?php
/**
  Plugin Name: StaticPress FTP
  Plugin URI: https://github.com/44uk/staticpress-ftp
  Description: StaticPress FTP upload plugin.
  Version: 0.0.1
  Author: 44uk <yukku0423@gmail.com>
  Author URI: http://44uk.net/
  License: GPL
  License URI: https://www.gnu.org/licenses/gpl-3.0.txt
  Tags: staticpress,ftp
  Donate link: http://example.com/donations
  */
add_action('plugins_loaded', function(){
    global $staticpress;
    $class_path = dirname(__FILE__).'/classes';
    if (! isset($staticpress)) {
        // StaticPress Required.
        return;
    }
    if (! class_exists('StaticPress_FTPClient')) {
        require($class_path.'/staticpress_ftp_client.class.php');
    }
    if (! class_exists('StaticPress_FTPAdmin')){
        require($class_path.'/staticpress_ftp_admin.class.php');
    }
    if (! class_exists('StaticPress_FTP')){
        require($class_path.'/staticpress_ftp.class.php');
    }
    new StaticPress_FTP(StaticPress_FTPAdmin::get_options());
    if (is_admin()) { new StaticPress_FTPAdmin(); }
});
