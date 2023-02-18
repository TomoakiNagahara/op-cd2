<?php
/** op-cd2:/Display.php
 *
 * @created    2023-01-02
 * @moved      2023-02-05 from op-cd1
 * @version    1.0
 * @package    op-cd2
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** Display message
 *
 * @created    2023-01-02
 */
function Display(string $message)
{
	//	...
	static $_display = null;
	static $_debug   = null;

	//	...
	if( $_display === null ){
		$_display = Request('display') ?? 1;
		$_debug   = Request('debug')   ?? 0;
	}

	//	...
	if(!strlen($message) ){
		return;
	}

	//	...
	if( $_display ){
		//	...
		echo $message . "\n";

		//	...
		if( $_debug > 1 ){
			DebugTrace(debug_backtrace());
		}
	}
}
