<?php
namespace Up;

/**
 * Autoload classes
 * 
 * Up\Autoload
 * 
 * @package    $Autoload
 * @relationUp $Exception, $Events
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */

class Autoload
{
	const EVENT_NAMESPACE        = __CLASS__;
	
	const EVENT_BEFORE_AUTOLOAD  = 'beforeAutoload';
	const EVENT_AFTER_AUTOLOAD   = 'afterAutoload';
	
	/**
	 * autoload classes
	 *
	 * @param string $classname
	 * @return boolean
	 */
	public static function autoload( $classname )
	{
		$eventCall = \Up\Events::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_BEFORE_AUTOLOAD
			, array( $classname )
			, array( __CLASS__, 'callback' )
		);
			
		if ( $eventCall === true ) return true;
		
		$filename = str_replace( array( '_', '\\' ), '/', $classname );
		
		if ( !self::inc( $filename ) ) return false;
		
		return true;
	}

	public static function autoloads( array $classes )
	{
		foreach ( $classes as $class )
			if ( !self::autoload( $class ) ) self::exception( 'uknow startup file: ' . $class );
	}
	
	public static function inc($__filename, $__ext = 'php')
	{
		//if ( !file_exists( $__filename . '.' . $__ext ) || !is_readable( $__filename . '.' . $__ext ) ) return false;
		
		$return = true;
		$time   = microtime( true );

		if ( !include_once( $__filename . '.' . $__ext ) ) $return = false;
		
		$loadTime = microtime( true ) - $time;
		
		\Up\Events::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_AFTER_AUTOLOAD
			, array( $__filename . '.' . $__ext, $loadTime )
		);
		
		return $return;
	}
	
	public static function callback( $result, $args )
	{
		if ( $result === true ) return false;
	}
	
	private static function exception( $msg, $code = 0 )
	{
		throw new Exception( $msg, $code );
	}
}