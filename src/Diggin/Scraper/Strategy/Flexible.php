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

use Zend\Dom\Css2Xpath,
    Zend\Uri\Http as UriHttp,
    Diggin\Scraper\Adapter,
    Diggin\Scraper\Adapter\Htmlscraping\Htmlscraping,
    Diggin\Scraper\Exception;

class Flexible extends AbstractStrategy
{
    protected $adapterReadMethod = 'getSimplexml';

    protected $_evaluator;
    
    public function setAdapter(Adapter $adapter)
    {
        if (!($adapter instanceof \Diggin\Scraper\Adapter\SimplexmlAdapter)) {
            $msg = get_class($adapter).' is not extends ';
            $msg .= 'Diggin_Scraper_Adapter_SimplexmlAbstract';
            throw new Exception\LogicException($msg);
        }

        $this->_adapter = $adapter;
    }

    public function getAdapter()
    {
        if (!isset($this->_adapter)) {
            $this->_adapter = new Htmlscraping();
        }

        return $this->_adapter;
    }
    
    /**
     * Extarct values according process
     *
     * @param Diggin_Scraper_Wrapper_SimpleXMLElement $values
     * @param Diggin_Scraper_Process $process
     * @return array
     * @throws Diggin_Scraper_Strategy_Exception
     */
    public function extract($values, $process)
    {
        $results = (array) $values->xpath(self::_xpathOrCss2Xpath($process->getExpression()));

        // count($results) === 0 is not found by xpath-node
        // $results[0] === false is not found by attributes
        if (count($results) === 0 or ($results[0] === false)) {
            $exp = self::_xpathOrCss2Xpath($process->getExpression());
            throw new Exception\RuntimeException("Couldn't find By Xpath, Process :".$exp);
        }

        return $results;
    }

    protected static function _xpathOrCss2Xpath($exp)
    {
        if (preg_match('/^id\(/', $exp)) {
            return preg_replace("/^id\(((?:'|\")(\w*)(?:'|\"))\)/", '//*[@id=$1]', $exp);
        } else if (preg_match('#^(?:\.$|\./)#', $exp)) {
            return $exp;
        } else if (preg_match('!^/!', $exp)) {
            return '.'.$exp;
        } else {
            if (ctype_alnum($exp)) {
                return ".//$exp";
            } else {
                return '.'.preg_replace('#//+#', '//', str_replace(chr(32), '', Css2Xpath::transform($exp)));
            }
        }
    }

    /**
     * Get Evaluator
     *
     * @return Diggin_Scraper_Evaluator_Simplexml
     */
    public function getEvaluator($values, $process)
    {
        $evaluator = new \Diggin\Scraper\Evaluator\Simplexml($values, $process);
        $evaluator->setBaseUri($this->_getBaseUri());
        return $evaluator;
    }

    /**
     * Get Base Uri object
     * 
     * @return Diggin_Uri_Http
     */
    protected function _getBaseuri()
    {
        if (!$this->_baseUri instanceof UriHttp) {
            $simplexml = $this->readResource();
            $headBase = new \Diggin\Scraper\Helper\Simplexml\HeadBaseHref($simplexml);
            $headBase->setOption(array('baseUrl' => $this->_baseUri));
            $this->_baseUri = new UriHttp($headBase->getBaseUrl());
        }

        return $this->_baseUri;
    }

}
