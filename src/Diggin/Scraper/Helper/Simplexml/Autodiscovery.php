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

use Zend\Uri\UriFactory;

/**
 * Helper for Autodiscovery
 *
 * @package    Diggin_Scraper
 * @subpackage Helper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */
class Autodiscovery extends SimplexmlAbstract
{

    const XPATH_BOTH = '//head//link[@rel="alternate" and (@type="application/rss+xml" or @type="application/atom+xml")]//@href';
    const XPATH_RSS = '//head/link[@rel="alternate" and @type="application/rss+xml" and contains(@title, "RSS")]/@href';
    const XPATH_ATOM = '//head/link[@rel="alternate" and @type="application/atom+xml" and contains(@title, "Atom")]/@href';

    /**
     * Perform helper when called as $scraper->autodiscovery() from Diggin_Scraper object
     * 
     * @param string $type
     * @param string|Zend_Uri_Http $baseUrl
     * @return mixed
     */
    public function direct($type = null, $baseUrl = null)
    {
        return $this->discovery($type, $baseUrl);
    }
    
    /**
     * discovery feed url
     * 
     * @param string $type
     * @param string|Zend\Uri\Http $baseUrl
     * @return mixed
     */
    public function discovery($type = null, $baseUrl = null)
    {
        if ($type === 'rss') {
            $xpath = self::XPATH_RSS;
        } else if ($type === 'atom') {
            $xpath = self::XPATH_ATOM;
        } else {
            $xpath = self::XPATH_BOTH; 
        }
        
        if ($links = $this->getResource()->xpath($xpath)) {
            
            $ret = array();
            foreach ($links as $v) {
                
                if (isset($baseUrl)) {
                    $uri = UriFactory::factory(current($v->href));
                    $ret[] = (string) $uri->resolve($baseUrl);
                } else {
                    $ret[] = current($v->href);
                }
            }
            
            return $ret;
        }
        
        return null;
    }
}
