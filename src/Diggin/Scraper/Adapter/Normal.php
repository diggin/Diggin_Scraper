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

class Normal extends StringAbstract
{
    protected $_config = array();
    
    /**
     * Readdata as just getBody() 
     * (not rawBody and not html converting)
     * 
     * @param string $response
     * @return string
     */
    public function getString($response)
    {   
        return $response->getBody();
    }

    public function setConfig($config = array())
    {
        if (! is_array($config))
            throw new Exception('Expected array parameter, given ' . gettype($config));

        foreach ($config as $k => $v)
            $this->_config[strtolower($k)] = $v;

        return $this;
    }
}
