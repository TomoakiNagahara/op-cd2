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

	/** Git cloned
	 *
	 * @created    2024-05-26
	 * @var        boolean
	 */
	static private $_git_cloned;

	/** Change directory git root.
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
	static function Auto() : bool
	{
		//	...
		try{
			//	...
			Debug(__METHOD__, false);

			self::Init();
			self::Clone();
			self::Fetch();
			/*
			self::Rebase();
			*/
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

		//	"path" is old arg. Change to workspace.
		if( $workspace = Request('path') ){
			Request('workspace', $workspace);
		}

		//	...
		foreach(['config','workspace','upstream','origin','branch'] as $key ){
			if( empty( /* self::$_config[$key] = */ Request($key) ) ){
				throw new Exception("This arguments is not set ({$key}). Please read README.md.");
			}
		}

		//	...
		$workspace = Request('workspace');
		self::$_git_root  = rtrim($workspace,'/').'/';
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
		$origin    = Request('origin');
		$branch    = Request('branch');

        //  ...
        $workspace = dirname( self::$_git_root);
        $directory = basename(self::$_git_root);

		//	Check if already cloned.
		if( file_exists(self::$_git_root) ){
			Debug(' * Already cloned.('.self::$_git_root.')', 0);
			self::$_git_cloned = true;
			return;
		}

		//	Check if workspace exists.
		if(!file_exists($workspace) ){
			//	Create workspace directory.
			if( self::Shell("mkdir -p {$workspace}") ){
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
		$host_name = Request('gitmodules')['host_name'] ?? Request('gitmodules')['hostname'] ?? null;
		if( $user_name ){
			//	op-asset-git was separated from 2026.
			foreach([
				'./asset/git/submodule/repo.sh',
				'./asset/init/repo.sh',
			] as $file_path){
				//	...
				if( file_exists($file_path) ){
					self::Shell("bash {$file_path} {$user_name} {$host_name}");
					break;
				}
			}

		//	Switch to origin .gitmodules file.
		$gitmodules = '.gitmodules_'.Request('gitmodules')['origin'];
		self::Shell("rm .gitmodules");
		self::Shell("cp {$gitmodules} .gitmodules");
		self::Shell("git submodule sync");
		}

		//	op-asset-git was separated from 2026.
		foreach([
			'./asset/git/init.php',
			'./asset/init/submodules.php',
		] as $file_path){
			//	...
			if( file_exists($file_path) ){
				self::Shell("php {$file_path}");
				break;
			}
		}

		//	Init submodules. Maybe, If nothing commit id, return fail.
		/*
		self::Shell('git submodule update --init --recursive');
		self::Shell('php asset/git/init.php');
		*/

		//	Init app by ci.php.
		/*
		self::Shell("php ci.php display={$display} debug={$debug}");
		*/

		//	Checkout default branch from .gitmodules.
		/*
		self::Shell("php git.php asset/git/branch.php");
		*/
		/*
		self::Shell("php git.php asset/git/rebase.php branch={$branch}");
		*/

		/*
		//	Set submodules upstream repository.
		$gitmodules = '.gitmodules_'.Request('gitmodules')['upstream'];
		self::Shell("php git.php asset/git/submodule/remote/add.php config={$gitmodules} name=upstream display={$display} debug={$debug} test=0");
		*/

		//	Set upstream
		$upstream   = Request('upstream'); // main repository
		$gitmodules = Request('gitmodules')['upstream']; // .gitmodules file name. ex: origin, upstream, local
		//	If use ssh.
		if( Request('gitmodules')['ssh'] ?? null ){
			$from = '.gitmodules_'.$gitmodules;
			self::Shell("sh asset/init/ssh.sh {$from}");
			$gitmodules = 'ssh'; // Change to .gitmodules_ssh
		}
		//	Initialize.
		self::ChangeDirectory();
		self::Shell("git remote add upstream {$upstream}");
		self::Shell("php git.php asset/git/submodule/remote/add.php config=.gitmodules_{$gitmodules} name=upstream test=0");

		//	Fetch upstream
		/* Do it with Fetch().
		self::Shell("git fetch upstream");
		self::Shell("git submodule foreach git fetch upstream");
		*/
	}

    /** Rebase to the latest.
     *
     * @created    2023-02-07
     */
    static function Fetch()
    {
        //	...
        Debug(__METHOD__, false);

        //	Change directory.
        chdir(self::$_git_root);

		//	...
		$branch    = Request('branch');

		//	Fetch
		if( self::$_git_cloned ){
			self::Shell("php git.php asset/git/update.php branch={$branch}");
		}else{
		//	self::Shell("php git.php asset/git/branch.php");
			self::Shell("php git.php asset/git/update.php branch={$branch}");
		}
    }

	/** Rebase to the latest.
	 *
	 * @created    2023-02-07
	 */
	static function Rebase()
	{
		//	...
		Debug(__METHOD__, false);

        /*
		if(!(Request('rebase') ?? 1) ){
			Debug(" * Skip rebase", false);
			return;
		}
        */

		//	...
		self::ChangeDirectory();

		//	...
		$branch = Request('branch');
		self::Shell("php git.php asset/git/rebase.php remote=origin branch={$branch}");
	}

	/** Code Inspect
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
		/*
		$submodule = Request('submodule');
		*/
		$ci        = Request('ci')        ?? 1;

		//	...
		if(!$ci ){
			Debug('Skip CD', false);
			return;
		}

		//	...
		if( $version ){
			$versions = explode(',', $version);
		}else{
			$versions = [''];
		}

		/*
		//	...
		$configs = self::SubmoduleConfig();
		*/

		//	...
		foreach( $versions as $version){
			//	' 82' --> '82'
			$version = trim($version);

			//	Top
			self::ChangeDirectory();
			/*
			self::Shell("php{$version} ci.php display={$display} debug={$debug} force=1");
			*/
			self::Shell("php{$version} ci.php display={$display} debug={$debug}");

			/*
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
			*/
		}

		//	...
		Display(" * All inspection is complete.");
	}

	/** Code Delivery
	 *
	 * @created    2023-02-07
	 * @param      string      $version Execute PHP version
	 */
	static function CD(string $version='')
	{
		//	...
		Debug(__METHOD__, false);

		//	...
		/*
		$display = Request('display');
		$debug   = Request('debug');
		*/
		$cd      = Request('cd')      ?? 1;
		$remote  = 'upstream';

		//	...
		if(!$cd ){
			Debug('Skip CD', false);
			return;
		}else if(!$version ){
			$version = $cd['php'] ?? '';
		}

		//	Get default branch name
		if(!defined('_OP_APP_BRANCH_') ){
			self::ChangeDirectory();
			require_once('asset/config/op.php');
		}

		//	Submodules
		$configs = self::SubmoduleConfig();
		foreach( $configs as $config ){
			//	...
			$branch = $config['branch'] ?? null;
			if(!$branch ){
				$branch = _OP_APP_BRANCH_;
			}
			//	...
			self::ChangeDirectory($config['path']);
			//	...
			/*
			self::Shell("php{$version} cd.php remote={$remote} branch={$branch} display={$display} debug={$debug}");
			*/
			self::Shell("git push {$remote} {$branch}");
		}

		//	Main
		self::ChangeDirectory();
		/*
		self::Shell("php{$version} cd.php remote={$remote} display={$display} debug={$debug}");
		*/
		$branch = Request('branch');
		self::Shell("git push {$remote} {$branch}");

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
		Log($cmd);

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

	/** Log
	 *
	 * @created    2025-03-08
	 * @param      string     $line
	 */
	static function Log(string $line)
	{
		//	...
		$path = self::$_git_root . '_cicd.log';

		//	...
		file_put_contents($path, $line . PHP_EOL, FILE_APPEND);
	}
}
