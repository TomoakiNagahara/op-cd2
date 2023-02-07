<?php
/** op-cd2:/Request.php
 *
 * Request function is parse the format in which the key and value are connected with equal.
 *
 * @created    2022-12-07
 * @moved      2023-02-05 from op-cd1
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** Return request value by key.
 *
 * @created    2022-12-07
 * @param      string      $key
 * @return     mixed
 */
function Request(string $key){
	//	...
	static $_argv;

	//	...
	if(!$_argv ){
		$_argv = GetArgv();
	}

	//	...
	return $_argv[$key] ?? null;
}

/** Parse argv.
 *
 * @created    2022-12-07
 * @return     array
 */
function GetArgv() : array {
	//	...
	$argv = [];

	//	...
	foreach($_SERVER['argv'] as $pair ){
		//	...
		if(!strpos($pair, '=') ){
			continue;
		}

		//	...
		list($key, $var) = explode('=', $pair);

		//	...
		$argv[$key] = $var;
	}

	//	...
	if( $path = $argv['config'] ?? null ){
		//	...
		if(!file_exists($path) ){
			$current = getcwd();
			throw new Exception("Config file does not exists. (current={$current}, {$path})");
		}

		//	Sandbox
		$config = call_user_func(function($path){ return require($path); }, $path);

		//	...
		foreach( $config as $key => $val ){
			//	...
			if( isset($argv[$key]) ){
				//	already setted.
				continue;
			}

			//	...
			$argv[$key] = $val;
		}
	}

	//	...
	return $argv;
}
