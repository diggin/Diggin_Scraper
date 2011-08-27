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

abstract class StringAbstract implements Adapter
{
    
    protected abstract function getString($response);

    /**
     * Reading Response as String
     * 
     * @param object $response
     * @return string
     */
    final public function readData($response)
    {
        $string = $this->getString($response);
        if (!is_string($string)) {
            // require_once 'Diggin/Scraper/Adapter/Exception.php';
            throw new Exception('adapter getString not return String');
        }
        
        return $string;
    }
}
