<?php
namespace zongphp\response\Build\driver;

use zongphp\response\build\Base;
use zongphp\session\Session;

class Redirect extends Base
{

    protected $options = [];

    // URL参数
    protected $params = [];
	

    public function __construct($data = '', $code = 302, array $header = [], array $options = [])
    {
        parent::__construct($data, $code, $header, $options);
		
		$this->cacheControl('no-cache,must-revalidate');
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        $this->header['Location'] = $this->getTargetUrl();

        return;
    }

    /**
     * 重定向传值（通过Session）
     * @access protected
     * @param  string|array  $name 变量名或者数组
     * @param  mixed         $value 值
     * @return $this
     */
    public function with($name, $value = null)
    {
        $session = new Session();

        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $session->flash($key, $val);
            }
        } else {
            $session->flash($name, $value);
        }

        return $this;
    }

    /**
     * 获取跳转地址
     * @access public
     * @return string
     */
    public function getTargetUrl()
    {
        if (strpos($this->data, '://') || (0 === strpos($this->data, '/') && empty($this->params))) {
            return $this->data;
        } else {
            return $this->buildUrl($this->data, $this->params);
        }
    }
	
	public function buildUrl($url='',$params=[]){
		if(strpos($url, '?')){
			$paramStr = empty($params) ? '' : '&' . http_build_query($params);
		}else{
			$paramStr = empty($params) ? '' : '?' . http_build_query($params);		
		}
		return $url.$paramStr;
	}

    public function params($params = [])
    {
        $this->params = $params;

        return $this;
    }

    /**
     * 记住当前url后跳转
     * @access public
     * @param string $url 指定记住的url
     * @return $this
     */
    public function remember($url = null)
    {
        Session::set('redirect_url', $url ?: Request::url());

        return $this;
    }

    /**
     * 跳转到上次记住的url
     * @access public
     * @param  string  $url 闪存数据不存在时的跳转地址
     * @return $this
     */
    public function restore($url = null)
    {
        $session = new Session();

        if ($session->has('redirect_url')) {
            $this->data = $session->get('redirect_url');
            $session->del('redirect_url');
        } elseif ($url) {
            $this->data = $url;
        }

        return $this;
    }
}
