<?php
/*
Plugin Name: StaticPress FTP
Author: 44uk
Plugin URI: https://github.com/44uk/staticpress-ftp
Description: StaticPress -> Server via FTP.
Version: 0.0.1
Author URI: http://44uk.net/
Text Domain: static-press-ftp
Domain Path: /languages

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2014 (email : yukku0423@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
add_action('plugins_loaded', function(){
  global $staticpress;
  if (! isset($staticpress)) { return; }

  if (! class_exists('ftp_helper')) {
    require(dirname(__FILE__).'/includes/ftp_helper.class.php');
  }
  if (! class_exists('staticpress_ftp_admin')){
    require(dirname(__FILE__).'/includes/staticpress_ftp_admin.class.php');
  }
  if (! class_exists('staticpress_ftp')){
    require(dirname(__FILE__).'/includes/staticpress_ftp.class.php');
  }

  new staticpress_ftp(staticpress_ftp_admin::get_options());
  if (is_admin()) { new staticpress_ftp_admin(); }
});
