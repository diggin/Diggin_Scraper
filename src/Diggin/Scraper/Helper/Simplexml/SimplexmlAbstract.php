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
 * @subpackage Helper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */

/**
 * @namespace
 */
namespace Diggin\Scraper\Helper\Simplexml;

// require_once 'Diggin/Scraper/Helper/HelperAbstract.php';

abstract class SimplexmlAbstract extends \Diggin\Scraper\Helper\HelperAbstract
{
    
    public function setPreAmpFilter($flag)
    {
        $this->setOption(array('preAmpFilter' => $flag));
        
        return $this;
    }

    public function getPreAmpFilter()
    {
        if (array_key_exists('preAmpFilter', $this->_option)) {
            return $this->_option['preAmpFilter'];
        }

        return false;
    }
    
    /**
     * Notes: array-parameter not support since ver 0.7
     */
    protected function asString($sxml)
    {
        if ($sxml instanceof \Diggin\Scraper\Adapter\Wrapper\SimpleXMLElement) {
            return $sxml->asXML();
        }

        if ($this->getPreAmpFilter() === true) {
            if (!is_array($sxml)) {
                return htmlspecialchars_decode($sxml->asXML(),
                            ENT_NOQUOTES);
            } else {
                $ret = array();
                foreach ($sxml as $s) {
                    $ret[] = htmlspecialchars_decode($s->asXML(),
                                ENT_NOQUOTES);
                }
                return $ret;
            }
        } else {
            if (!is_array($sxml)) {
                if (count($sxml) === 0 and key($sxml) === 0) {
                    return (string)$sxml;
                } else {
                    return $sxml->asXML();
                }
            } else {
                //not implement
                throw new \InvalidArgumentException();
            }
        }
    }
}
