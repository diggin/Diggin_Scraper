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

/** Diggin_Scraper_Helper_Simplexml_Simplexml_HeadBaseHref **/
// require_once 'Diggin/Scraper/Helper/Simplexml/HeadBaseHref.php';

/**
 * Helper for pagerize info
 *
 * @package    Diggin_Scraper
 * @subpackage Helper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */
class Pagerize
        extends HeadBaseHref
{
    
    const HATOM_PAGEELEMENT = '//*[contains(concat(" ", @class, " "), " hentry ")]';
    const HATOM_NEXTLINK = '//link[contains(concat(" ", translate(normalize-space(@rel),"NEXT","next"), " "), " next ")] | //a[contains(concat(" ", translate(normalize-space(@rel),"NEXT","next"), " "), " next ")]';

    const CACHE_TAG_PREFIX = 'Diggin_Scraper_Helper_Simplexml_Pagerize_';

    /**
     * @var string
     */
    //private static $_cacheTagPrefix = 'Diggin_Scraper_Helper_Simplexml_Pagerize_';

    /**
     * @var Zend_Cache_Core
     */
    private static $_cache;

    /**
     * @var array
     */
    private static $_siteinfokeys = array(); 

    public function direct($preferHeadBase = true, $preferhAtom = true)
    {
        return $this->getNextLink($preferHeadBase, $preferhAtom);
    }

    /**
     * Sets a cache object
     *
     * @param Zend_Cache_Core $cache
     */
    public static function setCache(\Zend\Cache\Core $cache)
    {
        self::$_cache = $cache;
    }
    
    public function getNextLink($preferHeadBase = true, $preferhAtom = true)
    {
        $baseurl = $this->getBaseUrl($preferHeadBase);
        if ($preferhAtom) {
            $nextLink = $this->hAtomNextLink();
        }

        // LIFO
        if (count(self::$_siteinfokeys) !== 0) {
            foreach (array_reverse(self::$_siteinfokeys) as $key) {
                $siteinfos = self::getSiteinfo($key); 
                if ($next = $this->getNextLinkFromSiteinfo($siteinfos, $baseurl)) {
                    $nextLink = $next;
                    break;
                }
            }
        }

        if (($baseurl === null) or ($nextLink === null)) {
            return null;
        } elseif (($baseurl === null) and (null == parse_url($nextLink, PHP_URL_HOST))) {
            return null;
        } elseif ($baseurl === null) {
            return $nextLink; //maybe hAtom only
        }

        // require_once 'Diggin/Uri/Http.php';
        $uri = new \Diggin\Uri\Http();
        $uri->setBaseUri($baseurl);
        return $uri->getAbsoluteUrl($nextLink);
    }
    
    //checks, 
    public function hAtomNextLink()
    {
        $nextpageelement = $this->getResource()->xpath(self::HATOM_NEXTLINK);
        if (count($nextpageelement) !== 0) {
            return $nextpageelement[0][@href];
        }

        return null;
    }

    /**
     * Get next url from siteinfo
     *
     * @param array $items
     * @param string $url base url
     * @return mixed
     */
    protected function getNextlinkFromSiteInfo($items, $url) 
    {
        foreach ($items as $item) {
            //hAtom 対策
            if ('^https?://.' != $item['url'] && (preg_match('>'.$item['url'].'>', $url) == 1)) {
                if (preg_match('/^id\(/', $item['nextLink'])) {
                    $item['nextLink'] = preg_replace("/^id\(((?:'|\")(\w*)(?:'|\"))\)/", '//*[@id=$1]', $item['nextLink']);
                }

                $nextLinks = $this->getResource()->xpath($item['nextLink']);
                if (count($nextLinks) !== 0) {
                    return $nextLinks[0][@href];
                }
            }
        }
        
        return null;
    }

    public static function appendSiteinfo($suffix, $siteinfo)
    {
        $key = self::CACHE_TAG_PREFIX.$suffix;

        if (array_key_exists($key, self::$_siteinfokeys)) {
            // require_once 'Diggin/Scraper/Helper/Simplexml/Exception.php';
            throw new \Diggin\Scraper\Helper\Simplexml\Exception("$key is already used.");
        }

        if (!self::getSiteinfo($key)) {
            self::$_cache->save($siteinfo, $key);
        }

        array_push(self::$_siteinfokeys, $key);
    }

    protected static function getSiteinfo($key)
    {
        return self::$_cache->load($key);
    }

    public function hasSiteinfo($suffix)
    {
        $ids = self::$_cache->getIds();
        //array_search()
    }

    public static function loadSiteinfo($suffix)
    {
        return self::$_cache->load(self::CACHE_TAG_PREFIX.$suffix);
    }
    
    

}
