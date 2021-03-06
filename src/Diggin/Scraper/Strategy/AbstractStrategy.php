<?php

/**
 * Diggin - Simplicity PHP Library
 * 
 * LICENSE
 *
 * This source file is subject to the new BSD license.
 * It is also available through the world-wide-web at this URL:
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
namespace Diggin\Scraper\Strategy;

use LimitIterator,
    Diggin\Scraper\ProcessAggregate,
    Diggin\Scraper\Adapter,
    Diggin\Scraper\Context,
    Diggin\Scraper\Exception;

abstract class AbstractStrategy
{
    protected $adapterReadMethod;

    /**
     * response
     *
     * @var Zend_Http_Response
     */
    private $_response;
    
    /**
     * Response Adapter
     *
     * @var Diggin_Scraper_Adapter_Interface
     */
    protected $_adapter;

    /**
     * Adapted Resouce
     *
     * @var Diggin_Scraper_Adapter_Interface
     */
    private $_adaptedResource;

    /**
     * base uri
     *
     * @var Zend_Uri
     */
    protected $_baseUri;
    
    /**
     * set 
     * 
     */
    protected abstract function setAdapter(Adapter $adapter);
    
    protected abstract function getAdapter();
    
    /**
     * construct
     * 
     * @param Zend\Http\Response
     */
    public function __construct($response)
    {
        $this->_response = $response;
    }

    public function setBaseUri($uri)
    {
        $this->_baseUri = $uri;
    }
    
    /**
     * Read resource from adapter'read
     * 
     * @return mixed
     */
    public function readResource()
    {
        if (!$this->_adaptedResource) {
            $readmethod = $this->adapterReadMethod;
            $this->_adaptedResource = $this->getAdapter()->$readmethod($this->getResponse());
        }
        return $this->_adaptedResource;
    }
    
    public function getResponse()
    {
        return $this->_response;
    }

    protected abstract function getEvaluator($values, $process);
    
    protected abstract function extract($values, $process);

    /**
     * get values (Recursive)
     *
     * @param mixed $context 
     *          [first:Diggin_Scraper_Context
     *           second:array]
     * @param Process $process
     * @return mixed $values
     * @throws Diggin_Scraper_Strategy_Exception
     * @throws Diggin_Scraper_Filter_Exception
     */
    public function getValues($context, $process)
    {
        if ($context instanceof Context) {
            $values = $this->extract($context->read(), $process);
            if ($values === false) {
                throw new Exception\UnexpectedValueException("Couldn't find By expression : ".$process->getExpression());
            }
        } else {
            $values = $this->extract($context, $process);
            if ($values === false) {
                return false;
            }
        }

       if ($process->getType() instanceof ProcessAggregate) {
            $returns = false;
            foreach ($values as $count => $val) {
                foreach ($process->getType() as $proc) {
                    if (false !== $getval = $this->getValues($val, $proc)) {
                        $returns[$count][$proc->getName()] = $getval;
                    }
                }

                if (($process->getArrayFlag() === false) && $count === 0) {
                    if(is_array($returns)) {
                        $returns = current($returns); break;
                    }
                }
            }

            return $returns;
        }

        $values = $this->getEvaluator($values, $process);
        if ($process->getFilters()) {
            $values = new \Diggin\Scraper\Filter\Iterator($values);
        }

        $arrayflag = $process->getArrayFlag();

        if ($arrayflag === false) {
            $values = new LimitIterator($values, 0, 1);
        } else if (is_array($arrayflag)) {
            $values = new LimitIterator($values, $arrayflag['offset'], $arrayflag['count']);
        }
 
        //@todo using iterator option
        $values = iterator_to_array($values); 
        if (false === $arrayflag) {
            return current($values);
        }
 
        return $values;
    }
}
