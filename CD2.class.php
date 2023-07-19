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
			$message = $e->getMessage();
			echo "\nCD2: {$message}\n";
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

	/** Clone
	 *
	 * @created    2023-02-05
	 */
	static function Clone()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		$workspace = Request('workspace');
		$display   = Request('display');
		$debug     = Request('debug');
		$origin    = Request('origin');
		$branch    = Request('branch');
		$directory = Request('directory');

		//	Check if already cloned.
		if( file_exists(self::$_git_root) ){
			Debug(' * Already cloned.('.self::$_git_root.')', 0);
			return;
		}

		//	Check if workspace exists.
		if(!file_exists($workspace) ){
			//	Create workspace directory.
			if( self::Shell('mkdir {$workspace}') ){
				throw new Exception("mkdir failed. ($workspace)");
			}
		}

		//	Change directory to workspace.
		if(!chdir($workspace) ){
			throw new Exception("chdir failed. ($workspace)");
		}

		//	Git clone.
		self::Shell("git clone {$origin} {$directory}");

		//	Change directory to cloned directory.
		self::ChangeDirectory();

        //  Get current branch.
        $current_branch = trim(`git rev-parse --abbrev-ref HEAD 2>&1`);

        //  Change target branch.
        if( $branch !== $current_branch ){
			self::Shell("git checkout origin/{$branch} -b {$branch}");
		}

		//	Change submodule resource.
		$user_name = Request('github');
		self::Shell("sh ./asset/git/submodule/repo.sh {$user_name}");

		//	Switch to origin .gitmodules file.
		$gitmodules = '.gitmodules_'.Request('gitmodules')['origin'];
		self::Shell("rm .gitmodules");
		self::Shell("cp {$gitmodules} .gitmodules");
		self::Shell("git submodule sync");

		//	Init submodules. Maybe, If nothing commit id, return fail.
		`git submodule update --init --recursive`;

		//	Init app by ci.php.
		self::Shell("php ci.php display={$display} debug={$debug}");

		//	Checkout default branch from .gitmodules.
		self::Shell("php git.php asset/git/rebase.php branch={$branch}");

		//	Set submodules upstream repository.
		$gitmodules = '.gitmodules_'.Request('gitmodules')['upstream'];
		self::Shell("php git.php asset/git/submodule/remote/add.php config={$gitmodules} name=upstream display={$display} debug={$debug} test=0");

		//	Set upstream
		$upstream = Request('upstream');
		self::ChangeDirectory();
		self::Shell("git remote add upstream {$upstream}");

		//	Fetch upstream
		self::Shell("git fetch upstream");
		self::Shell("git submodule foreach git fetch upstream");
	}

	/** Rebase to the latest.
	 *
	 * @created    2023-02-07
	 */
	static function Rebase()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		if(!(Request('rebase') ?? 1) ){
			Debug(" * Skip rebase", false);
			return;
		}

		//	...
		self::ChangeDirectory();

		//	...
		$branch = Request('branch');
		self::Shell("php git.php asset/git/rebase.php remote=origin branch={$branch}");
	}

	/** CI
	 *
	 * @created    2023-02-07
	 */
	static function CI()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		$display = Request('display');
		$debug   = Request('debug');
		$version = Request('version');
		$submodule = Request('submodule');

		//	...
		if( $version ){
			$versions = explode(',', $version);
		}else{
			$versions = [''];
		}

		//	...
		$configs = self::SubmoduleConfig();

		//	...
		foreach( $versions as $version){
			//	' 82' --> '82'
			$version = trim($version);

			//	Top
			self::ChangeDirectory();
			self::Shell("php{$version} ci.php display={$display} debug={$debug} force=1");

			//	Submodules
			foreach( $configs as $config ){
				//	...
				if( $submodule ){
					if( $submodule !== $config['path'] ){
						continue;
					}
				}

				//	...
				self::ChangeDirectory($config['path']);
				//	...
				self::Shell("php{$version} ci.php display={$display} debug={$debug} force=1");
			}
		}

		//	...
		Display(" * All inspection is complete.");
	}

	/** CD
	 *
	 * @created    2023-02-07
	 */
	static function CD()
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		if(!(Request('cd') ?? 1) ){
			Debug('Skip CD', false);
			return;
		}

		//	...
		$display = Request('display');
		$debug   = Request('debug');
		$remote  = 'upstream';

		//	Main
		self::ChangeDirectory();
		self::Shell("php cd.php remote={$remote} display={$display} debug={$debug}");

		//	Submodules
		$configs = self::SubmoduleConfig();
		foreach( $configs as $config ){
			//	...
			self::ChangeDirectory($config['path']);
			//	...
			self::Shell("php cd.php remote={$remote} display={$display} debug={$debug}");
		}

		//	...
		Display(" * All delivery is complete.");
	}

	/** Execute shell
	 *
	 * @created    2023-02-07
	 * @param      string      $cmd
	 */
	static function Shell($cmd)
	{
		//	...
		$result = [];
		$status = 0;
		exec("$cmd 2>&1", $result, $status);

		//	...
		Display("\n * {$cmd}\n");
		if( $result ){
			Display( join("\n", $result) . "\n" );
		}

		//	...
		if( Request('debug') ){
			$current = getcwd();
			Debug("{$current} - $cmd --> $status", false);
		}

		//	...
		if( $status ){
			throw new Exception($cmd);
		}
	}
}
