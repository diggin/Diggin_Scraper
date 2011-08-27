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
 * @subpackage Evaluator
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */

/**
 * @namespace
 */
namespace Diggin\Scraper\Evaluator;

use Zend\Uri\Uri,
    Zend\Uri\UriFactory;

class Simplexml extends AbstractEvaluator
{
    /**
     * @var Diggin_Uri_Http
     */
    private $_baseUri;

    public function setBaseUri(Uri $uri)
    {
        $this->_baseUri = $uri;
    }

    public function getBaseUri()
    {
        return $this->_baseUri;
    }

    protected function _eval($simplexml)
    {
        $type = $this->getProcess()->getType();

        switch (strtolower($type)) {
            case 'raw' :
                return $simplexml;
            case 'asxml' :
                return $simplexml->asXML();
            case 'text' :
                $value = strip_tags($simplexml->asXML());
                $value = str_replace(array(chr(9), chr(10), chr(13)), '', $value);
                return $value;
            case 'decode' :
            case 'disp' :
                $value = strip_tags($simplexml->asXML());
                $value = html_entity_decode(strip_tags($value), ENT_NOQUOTES, 'UTF-8');
                $value = str_replace(array(chr(9), chr(10), chr(13)), '', $value);
                return $value;
            case 'html' :
                $value = $simplexml->asXML();
                $value = str_replace(array(chr(10), chr(13)), '', $value);
                return preg_replace(array('#^<.*?>#', '#s*</\w+>\n*$#'), '', $value);
            default :
                if ('@' === $type['0']) {
                    $attribute = $simplexml[substr($type, 1)];
                    if ($attribute === null) {
                        $value = null;
                    } else {
                        if (in_array($type, array('@href', '@src'))) {
                            $uri = UriFactory::factory((string)$attribute);
                            $value = $uri->resolve($this->getBaseUri());
                            //$value = $this->getBaseUri()->getAbsoluteUrl($attribute);
                        } else {
                            $value = (string) $attribute;
                        }
                    }

                    return $value;
                } 
        }

        // require_once 'Diggin/Scraper/Evaluator/Exception.php';
        $process = $this->getProcess();
        throw new Exception($type." is unknown type ($process)");
    }

}
