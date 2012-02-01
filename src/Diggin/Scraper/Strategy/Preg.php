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
 * @version  $Id$
 */

/**
 * @namespace
 */
namespace Diggin\Scraper\Strategy;

use Diggin\Scraper\Adapter,
    Diggin\Scraper\Adapter\StringAbstract,
    Diggin\Scraper\Exception;

class Preg extends AbstractStrategy 
{
    protected $_evaluator;

    public function setAdapter(Adapter $adapter)
    {
        if (!($adapter instanceof StringAbstract)) {
            $msg = get_class($adapter).'Adapter is not extends ';
            $msg .= 'Diggin_Scraper_Adapter_StringAbstract';
            throw new Exception\DomainException($msg);
        }

        $this->_adapter = $adapter;
    }

    public function getAdapter()
    {
        if (!isset($this->_adapter)) {
            $this->_adapter = new \Diggin\Scraper\Adapter\Normal();
        }

        return $this->_adapter;
    }

    public function extract($string, $process)
    {
        if (is_array($string)) {
            $string = array_shift($string);
            preg_match_all($process->getExpression(), self::cleanString($string) , $results);
        } elseif (is_string($string)) {
            preg_match_all($process->getExpression(), self::cleanString($string) , $results);
        } else {
            throw new Exception\InvalidArgumentException('invalid value');
        }

        return $results;
    }

    /**
     * Body Cleaner for easy dealing with regex
     * 
     * @param string
     * @return string
     */
    private static function cleanString($resposeBody)
    {
        $results = str_replace(array(chr(10), chr(13), chr(9)), chr(32), $resposeBody);
        while (strpos($results, str_repeat(chr(32), 2), 0) != false){
            $results = str_replace(str_repeat(chr(32), 2), chr(32), $results);
        }

        return trim($results);
    }

    /**
     * Get Evaluator
     *
     * @return Diggin_Scraper_Evaluator_String
     */
    public function getEvaluator($values, $process)
    {
        $evaluator = new \Diggin\Scraper\Evaluator\String($values, $process);
        return $evaluator;
    }

}
