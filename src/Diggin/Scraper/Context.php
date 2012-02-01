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
namespace Diggin\Scraper;

class Context
{
    /**
     * scraper's strategy 
     *
     * @var Diggin_Scraper_Strategy_Abstract $_strategy
     */
    private $_strategy;
    
    /**
     * construct
     * 
     * @param Diggin_Scraper_Strategy_Abstract $strategy
     */
    public function __construct(Strategy\AbstractStrategy $strategy)
    {
        $this->_strategy = $strategy;
    }

    /**
     * Read adapted resource via strategy->readResource
     *
     * @return mixed 
     */
    public function read()
    {
        return $this->_strategy->readResource();
    }
}
