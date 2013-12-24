<?php
namespace up\oauth\server;

class error
{
	const UNKNOWN          = 0;
	const ACTION_NOT_FOUND = 1;
	const PARAM_NOT_SET    = 2;

	private static $errorMsg = array(
		  self::UNKNOWN          => 'unknown error'
		, self::ACTION_NOT_FOUND => 'action not found'
	);

	public static function getMsgByCode( $code )
	{
		return isset( self::$errorMsg[$code] ) ? self::$errorMsg[$code] : null;
	}
}