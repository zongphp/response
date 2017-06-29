<?php
namespace zongphp\response;
use zongphp\framework\build\Facade;

class ResponseFacade extends Facade {
	public static function getFacadeAccessor() {
		return 'Response';
	}
}