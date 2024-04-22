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
$config['path']      = '/www/workspace/2022/php70/';
$config['origin']    = '~/repo/op/skeleton/2022.git';
$config['upstream']  = 'app3:~/repo/op/skeleton/2022.git';
$config['github']    = 'TomoakiNagahara'; // GitHub account (user name)
$config['branch']    = 'php70'; // This is the main branch name. For each submodule branch name is taken from .gitmodules files.
$config['gitmodules']=[ // Which .gitmodules file.
	'origin'   => 'local',
	'upstream' => 'app3',
	'host_name'=> 'app3',
];
$config['display']   = '0';
$config['debug']     = '0';
$config['version']   = '70, 71, 72, 73, 74'; // PHP version to inspect.
$config['cd']['php'] = '70'; // PHP version when running CD.

//	...
return $config;
