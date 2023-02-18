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
$config['upstream']  = 'https://github.com/onepiece-framework/op-app-skeleton-2022';
$config['origin']    = '~/repo/op/skeleton/2022.git';
$config['github']    = 'TomoakiNagahara'; // GitHub account (user name)
$config['branch']    = 'php74'; // This is parent branch. Each submodules branch is .gitmodules.
$config['directory'] = '2022-php74';  // Git clone use this directory name
$config['gitmodules']=[ // Which .gitmodules file.
	'origin'   => 'local',
	'upstream' => 'repo',
];
$config['display']   = '1';
$config['debug']     = '1';
$config['version']   = '70, 71, 72, 73, 74'; // PHP version to inspect.

//	...
return $config;
