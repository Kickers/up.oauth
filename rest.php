<?php
class rest
{
	private $uriParams;
	private $path;

	const ERROR_UNKNOWN          = 0;
	const ERROR_ACTION_NOT_FOUND = 1;
	const ERROR_PARAM_NOT_SET    = 2;

	private static $errorMsg = array(
		  self::ERROR_UNKNOWN          => 'unknown error'
		, self::ERROR_ACTION_NOT_FOUND => 'action not found'
	);

	private static $statusGroups = array(
		  204 => array(

		)
		, 400 => array(
			  self::ERROR_PARAM_NOT_SET
			, self::ERROR_ACTION_NOT_FOUND
		)
		, 500 => array(
			self::ERROR_UNKNOWN
		)
	);

	private static $httpStatuses = array(
		  100 => 'Continue'
		, 101 => 'Switching Protocols'
		, 102 => 'Processing'

		, 200 => 'OK'
		, 201 => 'Created'
		, 202 => 'Accepted'
		, 203 => 'Non-Authoritative Information'
		, 204 => 'No Content'
		, 205 => 'Reset Content'
		, 206 => 'Partial Content'
		, 207 => 'Multi-Status'
		, 226 => 'IM Used'

		, 300 => 'Multiple Choices'
		, 301 => 'Moved Permanently'
		, 302 => 'Found'
		, 303 => 'See Other'
		, 304 => 'Not Modified'
		, 305 => 'Use Proxy'
		, 307 => 'Temporary Redirect'

		, 400 => 'Bad Request'
		, 401 => 'Unauthorized'
		, 402 => 'Payment Required'
		, 403 => 'Forbidden'
		, 404 => 'Not Found'
		, 405 => 'Method Not Allowed'
		, 406 => 'Not Acceptable'
		, 407 => 'Proxy Authentication Required'
		, 408 => 'Request Timeout'
		, 409 => 'Conflict'
		, 410 => 'Gone'
		, 411 => 'Length Required'
		, 412 => 'Precondition Failed'
		, 413 => 'Request Entity Too Large'
		, 414 => 'Request-URL Too Long'
		, 415 => 'Unsupported Media Type'
		, 416 => 'Requested Range Not Satisfiable'
		, 417 => 'Expectation Failed'
		, 418 => 'I\'m a teapot'
		, 422 => 'Unprocessable Entity'
		, 423 => 'Locked'
		, 424 => 'Failed Dependency'
		, 425 => 'Unordered Collection'
		, 426 => 'Upgrade Required'
		, 429 => 'Retry With'
		, 456 => 'Unrecoverable Error'

		, 500 => 'Internal Server Error'
		, 501 => 'Not Implemented'
		, 502 => 'Bad Gateway'
		, 503 => 'Service Unavailable'
		, 504 => 'Gateway Timeout'
		, 505 => 'HTTP Version Not Supported'
		, 506 => 'Variant Also Negotiates'
		, 507 => 'Insufficient Storage'
		, 509 => 'Bandwidth Limit Exceeded'
		, 510 => 'Not Extended'
	);


	public function __construct()
	{

	}

	public function setUriParams( array $params )
	{
		$this->uriParams = $params;
	}

	public function setProjectsRoot( $path )
	{
		$this->path = $path;
	}

	public function call( $action )
	{
		$status = 200;

		try {
			$Controller = new \rest\controller( $this->path );

			$result = $Controller->call( $action, $this->uriParams );
		} catch ( \Exception $e ) {
			$code   = $e->getCode();
			$status = $this->getStatusByErrorCode( $code );

			$result = array(
				'error' => array(
					  'code' => $code
					, 'msg'  => $e->getMessage()
				)
			);
		}

		$result = $result ? $result : array();

		if ( empty( $result ) )
			$status = 204;

		$result = json_encode( $result );

		$this->sendHeaders( $status );
		$this->sendResult( $result );
	}

	private function sendHeaders( $httpStatus )
	{
		$header = 'HTTP/1.1 ' . $httpStatus . ' ' . self::$httpStatuses[$httpStatus];

		header( $header, true );
		header( 'Content-Type: application/json' );
	}

	private function sendResult( $result )
	{
		echo $result;
	}

	private function getStatusByErrorCode( $errorCode )
	{
		foreach ( self::$statusGroups as $httpCode => $groups ) {
			if ( $code = array_search( $errorCode, $groups ) !== false )
				return $httpCode;
		}

		return null;
	}

	public static function error( $code = self::ERROR_UNKNOWN, $msg = null )
	{
		$msg = $msg ? $msg : self::$errorMsg[$code];

		throw new \Exception( $msg, $code );
	}
}