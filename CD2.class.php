<?php
/** op-cd2:/CD2.class.php
 *
 * @created    2023-02-05
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** Code Delivery
 *
 * @created    2023-02-05
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class CD2
{
	/** Git root.
	 *
	 * .git is located.
	 *
	 * @var string
	 */
	static private $_git_root;

	/** Chenge directory git root.
	 *
	 * @created    2023-02-07
	 * @param      string      $path
	 * @throws     Exception
	 */
	static private function ChangeDirectory($path='')
	{
		//	...
		if(!chdir(self::$_git_root.$path ) ){
			$current = getcwd();
			$gitroot = self::$_git_root.$path;
			throw new Exception("chdir failed. (current={$current}, directory={$gitroot})");
		}
		//	...
		Display(' * Change Directory: '.getcwd());
	}

	/** Get SubmoduleConfig
	 *
	 * @created    2023-02-16
	 * @throws     Exception
	 * @return     array
	 */
	static function SubmoduleConfig():array
	{
		//	...
		static $_configs;

		//	...
		if(!$_configs ){
			//	...
			self::ChangeDirectory();

			//	...
			require_once('asset/unit/git/function/SubmoduleConfig.php');

			//	...
			if(!$configs = \OP\UNIT\GIT\SubmoduleConfig() ){
				throw new Exception("Get SubmoduleConfig was failed.");
			}
		}

		//	...
		return $configs;
	}

	/** Auto
	 *
	 * @created    2023-02-05
	 */
	static function Auto()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		try{
			self::Init();
			self::Clone();
			self::Rebase();
			self::CI();
			self::CD();

			//	...
			Display(`git submodule status` ?? '');

		}catch( Throwable $e ){
			echo "\n" . $e->getMessage() . "\n";
			DebugTrace( $e->getTrace() );
			return false;
		}

		//	...
		return true;
	}

	/** Init
	 *
	 * @created    2023-02-05
	 */
	static function Init()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		foreach(['config','workspace','upstream','origin','branch','directory'] as $key ){
			if( empty( /* self::$_config[$key] = */ Request($key) ) ){
				throw new Exception("This arguments is not set ({$key}). Please read README.md.");
			}
		}

		//	...
		$workspace = Request('workspace');
		$directory = Request('directory');
		self::$_git_root  = rtrim($workspace,'/').'/'.$directory.'/';
	}
}
