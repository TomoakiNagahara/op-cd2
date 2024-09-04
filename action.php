<?php
/** op-cd2:/action.php
 *
 * # For crontab
 * 1. `ssh-agent -s` : Startup ssh-agent
 * 2. `ssh-add /Users/tomoaki/.ssh/id_rsa` : Add private key
 *
 * @created    2023-01-02
 * @moved      2023-02-05 from op-cd1
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

//	...
error_reporting(E_ALL);
ini_set('short_open_tag', 1);
ini_set('display_errors', 1);
ini_set('log_errors'    , 0);

//	...
chdir(__DIR__);

//	...
require_once('Error.php');
require_once('Debug.php');
require_once('Display.php');
require_once('Request.php');
require_once('CD2.class.php');

//	...
$exit = CD2::Auto() ? 0: 1;

//	...
exit($exit);
