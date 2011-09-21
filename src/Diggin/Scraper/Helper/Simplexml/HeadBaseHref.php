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
            rsort($bases);
            foreach ($bases as $base) {
                try {
                    $base = current($base->attributes()->href);
                    $uri = UriFactory::factory($base);
                    if (!$uri instanceof Http) {
                        continue;
                    }
                    return $uri;
                } catch (\Zend\Uri\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }
}
