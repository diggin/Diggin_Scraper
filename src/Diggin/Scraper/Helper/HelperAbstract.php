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
namespace Diggin\Scraper\Helper;

abstract class HelperAbstract
{
    /**
     * @var mixed $_resource
     */
    private $_resource;

    /**
     * option
     * 
     * @var array
     */
    protected $_option;

    /**
     * Constructor
     *
     * Add resource
     *
     * @param mixed $resource
     * @param array $option
     * @return void
     */
    public function __construct($resource, $option = array())
    {
        $this->_resource = $resource;
        $this->_option = $option;
    }

    /**
     * get resource for scraping
     *
     * @return mixed $_resource
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Set Configure
     *
     * @param array $option
     */
    public function setOption(array $option)
    {
        foreach ($option as $k => $v) {
            $this->_option[$k] = $v;
        }
    }

    /**
     * magic method _invoke for using over PHP5.3
     * call only direct() method
     *
     * @return mixed
     */
    public function __invoke()
    {
        return $this->direct();
    }
}
