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
namespace Diggin\Scraper\Adapter;

use Diggin\Scraper\Adapter;

abstract class SimplexmlAbstract implements Adapter
{

    protected abstract function getSimplexml($response);

    /**
     * Reading Response as SimpleXmlElement
     * 
     * @param object $response
     * @return SimplXmlElement
     */
    final public function readData($response)
    {
        
        try {
            $simplexml = $this->getSimplexml($response);
        } catch (\Exception $e){
            // require_once 'Diggin/Scraper/Adapter/Exception.php';
            throw new Exception($e);
        }
        
        if (!$simplexml instanceof \SimpleXMLElement) {
            // require_once 'Diggin/Scraper/Adapter/Exception.php';
            throw new Exception('adapter getSimplexml not return SimpleXMLElement');
        }
        
        return $simplexml;
    }
}
