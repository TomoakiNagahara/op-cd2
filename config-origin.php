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
$config['origin']    = 'https://github.com/TomoakiNagahara/op-app-skeleton-2022';
$config['github']    = 'TomoakiNagahara'; // GitHub account (user name)
$config['branch']    = 'master'; // This is parent branch. Each submodules branch is .gitmodules.
$config['directory'] = '2022-github';   // Git clone use this directory name
$config['gitmodules']=[ // Which .gitmodules file.
	'origin'   => 'github',
	'upstream' => 'origin',
];
$config['display']   = '1';
$config['debug']     = '1';
$config['version']   = ''; // PHP version to inspect.

//	...
return $config;
