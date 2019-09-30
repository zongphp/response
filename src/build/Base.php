<?php
namespace zongphp\response\build;
use zongphp\xml\Xml;

class Base {
	public function __construct() {
		if ( PHP_SAPI != 'cli' ) {
			defined( 'IS_GET' ) or define( 'IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET' );
			defined( 'IS_POST' ) or define( 'IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST' );
			defined( 'IS_DELETE' ) or define( 'IS_DELETE', $_SERVER['REQUEST_METHOD'] == 'DELETE' ? true : ( isset( $_POST['_method'] ) && $_POST['_method'] == 'DELETE' ) );
			defined( 'IS_PUT' ) or define( 'IS_PUT', $_SERVER['REQUEST_METHOD'] == 'PUT' ? true : ( isset( $_POST['_method'] ) && $_POST['_method'] == 'PUT' ) );
			defined( 'IS_AJAX' ) or define( 'IS_AJAX', isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' );
			defined( 'IS_WECHAT' ) or define( 'IS_WECHAT', isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( $_SERVER['HTTP_USER_AGENT'], 'MicroMessenger' ) !== false );
			defined( '__URL__' ) or define( '__URL__', trim( 'http://' . $_SERVER['HTTP_HOST'] . '/' . trim( $_SERVER['REQUEST_URI'], '/\\' ), '/' ) );
			defined( '__HISTORY__' ) or define( "__HISTORY__", isset( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : '' );
		}
	}

	/**
	 * 发送HTTP 状态码
	 *
	 * @param $code
	 */
	public function sendHttpStatus( $code ) {
		$status = [
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',
			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',
			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded',
		];

		if ( isset( $status[ $code ] ) ) {
			header( 'HTTP/1.1 ' . $code . ' ' . $status[ $code ] );
			header( 'Status:' . $code . ' ' . $status[ $code ] );
		}
	}

	/**
	 * Ajax输出
	 *
	 * @param mixed $data 数据
	 * @param string $type 数据类型 text xml json
	 */
	public function ajax( $data, $type = "JSON" ) {
		switch ( strtoupper( $type ) ) {
			case "TEXT" :
				$res = $data;
				break;
			case "XML" :
				header( 'Content-Type: application/xml' );
				$res = ( new Xml() )->toSimpleXml( $data );
				break;
			case 'JSON':
			default :
				header( 'Content-Type: application/json' );
				$res = json_encode( $data, JSON_UNESCAPED_UNICODE );
		}
		die( $res );
	}
	
	/**
     * 创建Response对象
     * @access public
     * @param  mixed  $data    输出数据
     * @param  string $type    输出类型
     * @param  int    $code
     * @param  array  $header
     * @param  array  $options 输出参数
     * @return Response
     */
    public static function create($data = '', $type = 'JSON', $code = 200, array $header = [], $options = [])
    {
		if (!headers_sent() && !empty($header)) {
            // 发送状态码
            http_response_code($code);
            // 发送头部信息
            foreach ($header as $name => $val) {		
                header($name . (!is_null($val) ? ':' . $val : ''));
            }
        }
		
		switch ( strtoupper( $type ) ) {
			case "TEXT" :
				$res = $data;
				break;
			case "HTML" :
				header( 'Content-Type: application/x-javascript;charset=utf-8' );
				$res = $data;
				break;
			case "XML" :
				header( 'Content-Type: application/xml;charset=utf-8' );
				$res = ( new Xml() )->toSimpleXml( $data );
				break;
			case 'JSONP':		
				$options = [
					'var_jsonp_handler'     => 'callback',
					'default_jsonp_handler' => 'jsonpReturn',
					'json_encode_param'     => JSON_UNESCAPED_UNICODE,
				];
				$var_jsonp_handler = input($options['var_jsonp_handler'], "");
				$handler = !empty($var_jsonp_handler) ? $var_jsonp_handler : $options['default_jsonp_handler'];

				$data = json_encode($data, $options['json_encode_param']);
				$res = $handler . '(' . $data . ');';
				
				break;
			case 'JSON':
				$options = [
					'json_encode_param'     => JSON_UNESCAPED_UNICODE
				];
				header( 'Content-Type: application/json;charset=utf-8' );
				$res = json_encode( $data, $options['json_encode_param'] );
				break;
			default :
				header( 'Content-Type: application/json;charset=utf-8' );
				$res = json_encode( $data, JSON_UNESCAPED_UNICODE );
		}
		
		die( $res );
    }
}