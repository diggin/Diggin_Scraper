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

use Zend\Uri\UriFactory,
    Zend\Uri\Http;

/**
 * Helper for Search Head-Base Tag, ignore bad-scheme
 *
 * @package    Diggin_Scraper
 * @subpackage Helper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */
class HeadBaseHref extends SimplexmlAbstract
{
    public function direct()
    {
        return $this->getHeadBaseUrl();
    }

    public function getBaseUrl($preferHeadBase = true)
    {
        if ($preferHeadBase) {
            $headBaseUrl = $this->getHeadBaseUrl();
            if ($headBaseUrl) {
                return $headBaseUrl;
            } 
        }

        if (array_key_exists('baseUrl',$this->_option)){
            return $this->_option['baseUrl'];
        }

        return null;
    }

    /**
     * Search Base Href
     * 
     * behavior is changed from April 1 2012.
     * current browswer seems not check
     *
     * This behavior reference from Firefox(YEAR - 2010)
     *
     * 1. Last base tag will be used.
     * 2. If schema is not valid, previous base tag will be used.
     * 3. Not http schema will be ignored.
     *
     * @return mixed
     */
    public function getHeadBaseUrl()
    {
        if ($bases = $this->getResource()->xpath('//base[@href]')) {
            $basehref = current($bases->attributes()->href);
            // @todo
            // should be check valid schema & logging
            return $basehref;
        }
        
        return null;
    }
}
