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

/** Diggin_Scraper_Helper_Simplexml_SimplexmlAbstract **/
// require_once 'Diggin/Scraper/Helper/Simplexml/SimplexmlAbstract.php';

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
    /**
     * 
     *
     */
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
     * firefoxではbaseタグが複数記述されていた場合は、最後のものを考慮する。
     * スキーマがよろしくない場合は、その前のものを考慮
     * httpスキーマではない場合は無視される。
     *
     * @return mixed
     */
    public function getHeadBaseUrl()
    {
        if ($bases = $this->getResource()->xpath('//base[@href]')) {
            rsort($bases);
            // require_once 'Zend/Uri.php';
            foreach ($bases as $base) {
                try {
                    $base = current($base->attributes()->href);
                    $uri = \Zend\Uri::factory($base);
                    return $uri;
                } catch (\Zend\Uri\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }
}
