<?php
namespace zongphp\response;
use zongphp\framework\build\Provider;

class ResponseProvider extends Provider {
	//延迟加载
	public $defer = true;

	public function boot() {
	}

	public function register() {
		$this->app->single( 'Response', function () {
			return new Response();
		} );
	}
}