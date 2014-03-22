<?php

namespace THCFrame\Request;

use THCFrame\Core\Base as Base;
use THCFrame\Events\Events as Events;
use THCFrame\Core\StringMethods as StringMethods;
use THCFrame\Request\RequestMethods as RequestMethods;
use THCFrame\Request\Exception as Exception;

/**
 * Description of Request
 *
 * @author Tomy
 */
class Request extends Base
{

    protected $_request;

    /**
     * @readwrite
     */
    public $_willFollow = true;

    /**
     * @readwrite
     */
    protected $_willShareSession = true;

    /**
     * @readwrite
     */
    protected $_headers = array();

    /**
     * @readwrite
     */
    protected $_options = array();

    /**
     * @readwrite
     */
    protected $_referer;

    /**
     * @readwrite
     */
    protected $_agent;

    /**
     * 
     * @param type $method
     * @return \THCFrame\Request\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @return \THCFrame\Request\Request
     */
    protected function _setOption($key, $value)
    {
        curl_setopt($this->_request, $key, $value);
        return $this;
    }

    /**
     * 
     * @param type $key
     * @return type
     */
    protected function _normalize($key)
    {
        return 'CURLOPT_' . str_replace('CURLOPT_', '', strtoupper($key));
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Request\Request
     */
    protected function _setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                $this->_setOption(CURLOPT_NOBODY, true);
                break;
            case 'GET':
                $this->_setOption(CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                $this->_setOption(CURLOPT_POST, true);
                break;
            default:
                $this->_setOption(CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        return $this;
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return \THCFrame\Request\Request
     */
    protected function _setRequestOptions($url, $parameters)
    {
        $this->_setOption(CURLOPT_URL, $url)
                ->_setOption(CURLOPT_HEADER, true)
                ->_setOption(CURLOPT_RETURNTRANSFER, true)
                ->_setOption(CURLOPT_USERAGENT, $this->agent);

        if (!empty($parameters)) {
            $this->_setOption(CURLOPT_POSTFIELDS, $parameters);
        }

        if ($this->willFollow) {
            $this->_setOption(CURLOPT_FOLLOWLOCATION, true);
        }

        if ($this->willShareSession) {
            $this->_setOption(CURLOPT_COOKIE, session_name() . '=' . session_id());
        }

        if ($this->referer) {
            $this->_setOption(CURLOPT_REFERER, $this->referer);
        }

        foreach ($this->_options as $key => $value) {
            $this->_setOption(constant($this->_normalize($key)), $value);
        }

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Request\Request
     */
    protected function _setRequestHeaders()
    {
        $headers = array();

        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $this->_setOption(CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->agent = RequestMethods::server('HTTP_USER_AGENT', 'Curl/PHP ' . PHP_VERSION);
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public function delete($url, $parameters = array())
    {
        return $this->request('DELETE', $url, $parameters);
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public function get($url, $parameters = array())
    {
        if (!empty($parameters)) {
            $url .= StringMethods::indexOf($url, '?') ? '&' : '?';
            $url .= is_string($parameters) ? $parameters : http_build_query($parameters, '', '&');
        }
        return $this->request('GET', $url);
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public function head($url, $parameters = array())
    {
        return $this->request('HEAD', $url, $parameters);
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public function post($url, $parameters = array())
    {
        return $this->request('POST', $url, $parameters);
    }

    /**
     * 
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public function put($url, $parameters = array())
    {
        return $this->request('PUT', $url, $parameters);
    }

    /**
     * 
     * @param type $method
     * @param type $url
     * @param type $parameters
     * @return \THCFrame\Request\Request\Response
     * @throws Exception\Response
     */
    public function request($method, $url, $parameters = array())
    {
        session_write_close();

        Events::fire('framework.request.request.before', array($method, $url, $parameters));

        $request = $this->_request = curl_init();

        if (is_array($parameters)) {
            $parameters = http_build_query($parameters, '', '&');
        }

        $this->_setRequestMethod($method)
                ->_setRequestOptions($url, $parameters)
                ->_setRequestHeaders();

        $response = curl_exec($request);

        if (!headers_sent()) {
            session_start();
        }

        if ($response) {
            $response = new Response(array(
                'response' => $response
            ));
        } else {
            throw new Exception\Response(ucfirst(curl_error($request)));
        }

        Events::fire('framework.request.request.after', array($method, $url, $parameters, $response));

        curl_close($request);
        return $response;
    }

}
