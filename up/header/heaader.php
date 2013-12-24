<?php
namespace up;

class header
{
	private $httpStatus  = 200;
	private $httpVersion = '1.1';
	private $headers     = array();
	
	private $httpStatuses = array(
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


	public function __construct( array $headers = array() )
	{
		$this->setByArray( $headers );
	}

	public function send( $httpStatus = null )
	{
		if ( $httpStatus ) $this->setStatus( $httpStatus );

		$this->sendStatus();
		$this->sendHeaders();
	}

	public function setStatus( $code = 200, $version = '1.1' )
	{
		$this->httpStatus  = (int) $code;
		$this->httpVersion = $version;

		return $this;
	}

	public function setByArray( array $headers )
	{
		foreach( $headers as $headerName => $headerValue )
			$this->set( $headerName, $headerValue );

		return $this;
	}

	public function set( $headerName, $headerValue )
	{
		$headerName = $this->prepareHeaderName( $headerName );

		$this->headers[$headerName] = $headerValue;

		return $this;
	}

	public function add( $headerName, $headerValue )
	{
		$headerName = $this->prepareHeaderName( $headerName );

		if ( $this->isExist( $headerName ) ) {
			if ( is_array( $this->headers[$headerName] ) ) {
				$this->headers[$headerName][] = $headerValue;
			} else {
				$this->headers[$headerName] = array( $this->headers[$headerName], $headerValue );

			}
		} else {
			$this->headers[$headerName] = array( $headerValue );
		}

		return $this;
	}

	public function remove( $headerName )
	{
		if ( $this->isExist( $headerName ) ) unset( $this->headers[$headerName] );

		return $this;
	}

	public function isExist( $headerName )
	{
		return isset( $this->headers[$headerName] );
	}


	private function sendStatus()
	{
		$httpStatus  = $this->httpStatus;
		$httpVersion = $this->httpVersion;

		if ( !$httpStatus || !isset( $this->httpStatuses[$httpStatus] ) ) return false;
		if ( !$httpVersion ) return false;

		$header = 'HTTP/' . $httpVersion . ' ' . $httpStatus . ' ' . $this->httpStatuses[$httpStatus];

		header( $header, true );
	}

	private function sendHeaders()
	{
		foreach( $this->headers as $headerName => $headerValues ) {
			if ( is_array( $headerValues ) ) {
				foreach( $headerValues as $headerValue )
					$this->sendHeader( $headerName, $headerValue, false );

				continue;
			} else {
				$this->sendHeader( $headerName, $headerValues, true );
			}
		}

		return true;
	}

	private function sendHeader( $headerName, $headerValue, $replace = true )
	{
		$header = $headerName . ': ' . $headerValue;

		header( $header, $replace );
	}

	private function prepareHeaderName( $headerName )
	{
		$headerName = strtolower( $headerName );
		$headerName = explode( '-', $headerName );

		$headerName = array_map( 'ucfirst', $headerName );
		$headerName = implode( '-', $headerName );

		return $headerName;
	}
}