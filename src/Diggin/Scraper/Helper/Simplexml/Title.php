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

// require_once 'Diggin/Scraper/Helper/Simplexml/SimplexmlAbstract.php';

/**
 * Helper for Search Title
 * got title string like as web browser
 *
 * @package    Diggin_Scraper
 * @subpackage Helper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */
class Title extends SimplexmlAbstract
{
    public function direct()
    {
        if ($titles = $this->getResource()->xpath('//title')) {
                $value = $this->asString(current($titles));
                $value = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
                $value = str_replace(array(chr(9), chr(10), chr(13)),'', $value);
            return trim(preg_replace(array('#^<.*?>#', '#s*</\w+>\n*$#'), '', $value));
        }

        return null;
    }
}
