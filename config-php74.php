<?php
/** op-cd2:/config.php
 *
 * @created    2023-01-02
 * @moved      2023-02-05 from op-cd1
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

//	...
$config = [];
$config['workspace'] = '/www/workspace/';
$config['upstream']  = 'repo:~/repo/op/skeleton/2022.git';
$config['origin']    = '~/repo/op/skeleton/2022.git';
$config['branch']    = 'php74'; // Switch to this branch
$config['directory'] = 'php74'; // Git clone use this directory name
$config['display']   = '1';
$config['debug']     = '1';
$config['version']   = '74';

//	...
return $config;
