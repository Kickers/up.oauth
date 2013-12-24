<?php
namespace up;

/**
 * Events class
 * 
 * Up\Events
 * 
 * @package    $Events
 * @relation   $Exception
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class events
{
	private static $events             = array();
	private static $eventsBeforeNotify = array();
	private static $callSelfEvent      = true;
	
	const EVENT_NAMESPACE   = __CLASS__;
	
	const EVENT_BEFORE_CALL = 'beforeCall';
	const EVENT_AFTER_CALL  = 'afterCall';

	/**
	 * add new event
	 *
	 * @param string $eventName
	 * @param callable $callback
	 * @return Events
	 */
	public static function bind( $namespace, $eventName, $callback )
	{
		if ( !isset( self::$events[$namespace] ) ) self::$events[$namespace] = array();
		if ( !isset( self::$events[$namespace][$eventName] ) ) self::$events[$namespace][$eventName] = array();
		
		self::$events[$namespace][$eventName][] = $callback;
	}

	public static function bindBeforeNotify( $callback, $namespaces = null )
	{
		self::$eventsBeforeNotify[] = array(
			  'callback'  => $callback
			, 'namespace' => $namespaces
		);
	}

	/**
	 * remove all events or all events by name
	 *
	 * @param null|strinf $eventName
	 */
	public static function unbind( $namespace = null, $eventName = null )
	{
		if ( $namespace === null && $eventName === null ) self::$events = array();
		if ( $namespace !== null && $eventName === null ) unset( self::$events[$namespace] );
		if ( $namespace !== null && $eventName !== null ) unset( self::$events[$namespace][$eventName] );
	}
	
	/**
	 * check is has event
	 *
	 * @param string $eventName
	 * @return boolean
	 */
	public static function hasEventListner( $namespace, $eventName = null )
	{
		if ( $eventName === null ) {
			if ( isset( self::$events[$namespace] ) ) return true;
		}
		elseif ( isset( self::$events[$namespace][$eventName] ) && !empty( self::$events[$namespace][$eventName] ) ) return true;
		
		return false;
	}
	
	/**
	 * call all events by name
	 *
	 * @param string $eventName
	 * @param array $args
	 * @return mixed
	 */
	public static function notify( $namespace, $eventName, array $args = array(), $callback = null )
	{
		self::callBeforeNotify( $namespace, $eventName, $args );

		if ( !self::hasEventListner( $namespace, $eventName ) ) return null;

		return self::callCallback( self::$events[$namespace][$eventName], $args, $callback, $namespace, $eventName );
	}
	
	/**
	 * call last event by name
	 *
	 * @param string $eventName
	 * @param array $args
	 */
	public static function notifyOnlyLast( $namespace, $eventName, array $args = array(), $callback = null )
	{
		self::callBeforeNotify( $namespace, $eventName, $args );

		if ( !self::hasEventListner( $namespace, $eventName ) ) return null;

		return self::callCallback( array( end( self::$events[$namespace][$eventName] ) ), $args, $callback, $namespace, $eventName );
	}
	
	/**
	 * call first event by name
	 *
	 * @param string $eventName
	 * @param array $args
	 */
	public static function notifyOnlyFirst( $namespace, $eventName, array $args = array(), $callback = null )
	{
		self::callBeforeNotify( $namespace, $eventName, $args );

		if ( !self::hasEventListner( $namespace, $eventName ) ) return null;

		return self::callCallback( array( self::$events[$namespace][$eventName][0] ), $args, $callback, $namespace, $eventName );
	}

	private static function callCallback( array $events, array $args, $callbackEvent, $namespace, $eventName )
	{
		$result = self::call( $events, $args, $callbackEvent, $namespace, $eventName );

		return $result;
	}

	private static function callBeforeNotify( $namespaceSend, $eventSend, $argsSend )
	{
		foreach( self::$eventsBeforeNotify as $events ) {
			foreach( $events['namespace'] as $namespace => $event ) {
				$call = false;

				if ( $namespace === null ) $call = true;
				elseif ( is_string( $namespace ) && $namespace == $namespaceSend ) $call = true;
				elseif ( is_array( $namespace ) ) {
					foreach( $namespace as $eventNamespace => $eventName ) {
						if ( is_numeric( $eventNamespace ) && $eventName == $namespaceSend ) $call = true;
						elseif ( $eventNamespace == $namespaceSend ) {
							if ( is_string( $eventName ) && $eventName == $event ) $call = true;
							elseif ( is_array( $eventName ) && in_array( $event, $eventName ) ) $call = true;
						}

						if ( $call === true ) break;
					}
				}

				if ( $call === true ) {
					if ( $events['callback'] !== null && !is_callable( $events['callback'] ) )
						self::exception( 'bad callback event function' );

					call_user_func_array( $events['callback'], array( $namespaceSend, $eventSend, $argsSend ) );
				}
			}
		}
	}
	
	private static function call( array $events, array $args, $callbackEvent, $namespace, $eventName )
	{
		if ( $callbackEvent !== null && !is_callable( $callbackEvent ) )
			self::exception( 'bad callback event function' );
		
		$countEvents = count( $events ) - 1;
		$result      = null;
		
		foreach ( $events as $num => $callback ) {
			if ( !is_callable( $callback ) ) self::exception( 'bad callback function' );

			$result = call_user_func_array( $callback, $args );

			//if ( $countEvents == $num ) break;
			
			if ( $callbackEvent !== null ) {
				$continue = call_user_func_array( $callbackEvent, array(
					&$result, &$args
				) );
				
				if ( $continue === false ) break;
			}
		}
		
		if ( self::$callSelfEvent === true ) {
			self::$callSelfEvent = false;
			self::notify( 
				  self::EVENT_NAMESPACE
				, self::EVENT_AFTER_CALL
				, array( $namespace, $eventName, $result )
			);
		}
		
		self::$callSelfEvent = true;
		
		return $result;
	}
	
	/**
	 * exception
	 *
	 * @param string $msg
	 */
	private static function exception( $msg )
	{
		throw new \Exception( $msg );
	}
}