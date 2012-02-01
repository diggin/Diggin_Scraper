<?php

/**
 * Diggin - Simplicity PHP Library
 * 
 * LICENSE
 *
 * This source file is subject to the new BSD license.
 * http://diggin.musicrider.com/LICENSE
 * 
 * @category   Diggin
 * @package    Diggin_Scraper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */

/**
 * @namespace
 */
namespace Diggin\Scraper;

use Zend\Http\Client as HttpClient,
    Zend\Http\Response as HttpResponse,
    Diggin\Scraper\Process,
    Diggin\Scraper\ProcessAggregate,
    Diggin\Scraper\Exception;

/**
 * @category  Diggin
 * @package   Diggin_Scraper
 * @copyright 2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license   http://diggin.musicrider.com/LICENSE     New BSD License
 */ 
class Scraper extends ProcessAggregate
{

    /**
     * target url of scraping
     * 
     * @var string 
     */
    protected $_url;
    
    /**
     */
    protected $_throwTargetExceptionsOn = true;

    /**
     * strategy name to use for changing strategy
     *
     * @param string $_strategyName
     */
    private static $_strategyName;
    
    /**
     * adapter for response
     *
     * @param Diggin_Scraper_Adapter_Interface $_adapter
     */
    private static $_adapter;

    /**
     * strategy for scraping
     *
     * @param Diggin_Scraper_Strategy_Abstract $_strategy
     */
    protected $_strategy = null;
    
    /**
     * helper loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected $_helperLoader;

    /**
     * Getting the URL for scraping
     * 
     * @return string $this->_url
     */
    private function _getUrl()
    {
        return $this->_url;
    }

    /**
     * Set the Url for scraping
     * 
     * @param string $url
     */
    public function setUrl ($url) 
    {
        $this->_url = $url;
    }

    /**
     * HTTP client object to use for retrieving
     *
     * @var Zend_Http_Client
     */
    protected static $_httpClient = null;

    public function __construct()
    {
    }

    public function throwTargetExceptionsOn($flag)
    {
        $this->_throwTargetExceptionsOn = (boolean) $flag;
    }

    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feeds.
     *
     * @param  Zend_Http_Client $httpClient
     * @return null
     */
    public static function setHttpClient(HttpClient $httpClient)
    {
        self::$_httpClient = $httpClient;
    }

    /**
     * Gets the HTTP client object. If none is set, a new Zend_Http_Client will be used.
     *
     * @return Zend_Http_Client_Abstract
     */
    public static function getHttpClient()
    {
        if (!self::$_httpClient instanceof HttpClient) {
            /**
             * @see Zend_Http_Client
             */
            self::$_httpClient = new HttpClient();
        }

        return self::$_httpClient;
    }

    /**
     * changing Strategy
     * 
     * @param string $strategyName
     * @param Diggin_Scraper_Adapter $adapter
     */
    public static function changeStrategy($strategyName, $adapter = null)
    {
        if ($strategyName === false or $strategyName === null) {
            self::$_strategyName = null;
            self::$_adapter = null;
        } else {
            self::$_strategyName = $strategyName;
            self::$_adapter = $adapter;
        }
    }

    /**
     * calling this scraper's strategy
     * 
     * @param Zend_Http_Response $response
     * @param string $strategyName
     * @param Object Diggin_Scraper_Adapter_Interface (optional)
     * @throws Diggin_Scraper_Exception
     */
    private function _callStrategy($response, $strategyName, $adapter = null)
    {
        if (!class_exists($strategyName)) {
            throw new Exception\DomainException("Unable to load strategy '$strategyName': {$e->getMessage()}");
        }

        $strategy = new $strategyName($response);
        $strategy->setBaseUri($this->_getUrl());

        if ($adapter) $strategy->setAdapter($adapter);
        if (method_exists($strategy->getAdapter(), 'setConfig')) {
            $strategy->getAdapter()->setConfig(array('url' => $this->_getUrl()));
        }

        $this->_strategy = $strategy;
    }

    /**
     * Return this scraper's strategy
     * 
     * @param Zend_Http_Response $response
     * @return Diggin_Scraper_Strategy
     */
    public function getStrategy($response)
    {
        if (!$this->_strategy instanceof Strategy\AbstractStrategy) {
            $strategy = new Strategy\Flexible($response);
            $strategy->setBaseUri($this->_getUrl());
            $strategy->getAdapter()->setConfig(array('url' => $this->_getUrl()));
            
            $this->_strategy = $strategy;
        }
        
        return $this->_strategy;
    }
    
    /**
     * making request
     * 
     * @param string $url
     * @return Zend_Http_Response $response
     * @throws Diggin_Scraper_Exception
     */
    protected function _makeRequest($url = null)
    {
        $client = self::getHttpClient();
        
        if ($url) {
            $this->setUrl($url);
            $client->setUri($url);
        } else {
            $this->setUrl($client->getUri());
        }

        $response = $client->send();

        if (!$response->isSuccess()) {
             /**
              * @see Diggin_Scraper_Exception
              */
             throw new Exception\RuntimeException("Http client reported an error: '{$response->getMessage()}'");
        }
        
        return $response;
    }

    /**
     * Get response via mixed pattern
     * 
     * @param mixed
     */
    protected function getResponse($resource)
    {
        //psuedo reponse
        if (is_array($resource) and !isset($resource['body'])) {
            $resource['body'] = $resource['0'];
            if (!array_key_exists('header', $resource)) {
                $resource['header'] = "HTTP/1.1 200 OK\r\nContent-type: text/html";
            }
            $responseStr = $resource['header']."\r\n\r\n".$resource['body'];
            $resource = HttpResponse::fromString($responseStr);
        }
        
        // if set uri
        if (!$resource instanceof HttpResponse) {
            $resource = $this->_makeRequest($resource);
        }
        
        return $resource;
    }
    
    /**
     * $scraper->process($expression, $key, $value_type, $filter1, $filter2,,,)
     */
    public function process($args)
    {
        $args = func_get_args();
        
        if ($args[0] instanceof Process) {
            $this->_processes[] = $args[0];
            return $this;
        }

        // fallback old-style process passed
        if ((count($args) >= 2) and is_string($args[1]) and preg_match('#.+=>.*#', $args[1])) {
            return call_user_func_array(array('parent', 'process'), $args);
        }

        // Validate, process arguments
        if (1 === count($args)) {
            throw new Exception\BadMethodCallException("Process requires over 2 arguments");
        } elseif (2 === count($args)) {
            if (is_array($args[1]) and (! current($args[1]) instanceof ProcessAggregate)) {
                throw new Exception\InvalidArgumentException("Child Process's value shold be instanceof Diggin\Scraper\ProcessAggregate");
            }
        }

        $expression = array_shift($args);
        
        // check child process
        if (is_array($args[0])) {
            $name = key($args[0]);

            if ((substr($name, -2) == '[]')) {
                $name = substr($name, 0, -2);
                $arrayflag = true;
            } else {
                $arrayflag = false;
            }
            $childprocess = current($args[0]);

            $process = new Process();
            $process->setExpression($expression);
            $process->setName($name);
            $process->setArrayflag($arrayflag);
            $process->setType($childprocess);
            $process->setFilters(false);
            $this->_processes[] = $process;
        } else {

            $name = array_shift($args);
            $types = array_shift($args);
            $filters = (count($args > 0)) ? $args : false;

            if ((substr($name, -2) == '[]')) {
                $name = substr($name, 0, -2);
                $arrayflag = true;
            } else {
                $arrayflag = false;
            }
            
            $process = new Process();
            $process->setExpression($expression);
            $process->setName(trim($name));
            $process->setArrayflag($arrayflag);
            $process->setType($types);
            $process->setFilters($filters);
            $this->_processes[] = $process;
        }

        return $this;
    }


    /**
     * scraping
     * 
     * @param (string | Zend_Http_Response | array) $resource
     *      setting URL, Zend_Http_Response, array($html)
     * @param string (if $resource is not URL, please set URL for recognize)
     * @return array $this->results Scraping data.
     * @throws Diggin_Scraper_Exception
     *          Diggin_Scraper_Strategy_Exception
     *          Diggin_Scraper_Adapter_Exception
     */
    public function scrape($resource = null, $baseUrl = null)
    {
        $resource = $this->getResponse($resource);
        
        if (isset($baseUrl)) {
            $this->setUrl($baseUrl);
        }

        if (!is_null(self::$_strategyName)) {
            $this->_callStrategy($resource, self::$_strategyName, self::$_adapter);
        } else {
            $this->_strategy = null;
        }
        $context = new Context($this->getStrategy($resource));
        $results = array(); //
        foreach ($this as $process) {
            try {
                $values = $this->_strategy->getValues($context, $process);
                $results[$process->getName()] = $values;
            } catch (Exception $dse) {
                if ($this->_throwTargetExceptionsOn === true) {
                    throw $dse;
                }
            }
        }

        return $results;
    }

    /**
     * get this helper's plugin loader
     *
     * @return Zend_Loader_PluginLoader 
     */
    public function getHelperLoader()
    {
        if (!$this->_helperLoader) {
            //initialize helper
            $this->_helperLoader = 
                new \Zend\Loader\PluginLoader(array(
                'Diggin_Scraper_Helper_Simplexml' => 'Diggin/Scraper/Helper/Simplexml'));
        }

        return $this->_helperLoader;
    }

    /**
     * getHelper() - get Helper by name
     *
     * @param string $name
     * @return Diggin_Scraper_Helper_HelperAbstract
     */
    public function getHelper($name)
    {
        $class = $this->getHelperLoader()->load($name);

        return new $class($this->strategy->readResource(), 
                          array('baseUrl' => $this->_getUrl()));
    }

    /**
     * call helper direct
     *
     * @param string $method
     * @param array $args
     */ 
    public function __call($method, $args)
    {
        $helper = $this->getHelper($method);
        if (!method_exists($helper, 'direct')) {
            throw new Exception\DomainException('Helper "'.$method.'" does not support overloading via direct()');
        }

        return call_user_func_array(array($helper, 'direct'), $args);
    }

}
