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

class Process
{
    private $_expression;
    private $_name;
    private $_arrayFlag;
    private $_type;
    private $_filters;

    /**
     * toString
     * UnTokenize process For using Exception errstr.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getType() instanceof Aggregate) {
            $ret = "";
            foreach ($this->getType()->getProcesses() as $process) {
                $ret .= $process->__toString($process);
            }

            return '\''.$this->getExpression().'\', '.
               "'".$this->getName().' => " '.$ret.'"';
        }

        if ($this->getFilters()) {
            if (count($this->getFilters()) !== 1) {
               $filters= implode(', ', $this->getFilters());
            } else {
               $filters = current($this->getFilters());
            }
            $filters = ($filters instanceof \Closure) ? 'closure' : $filters ;

            return '\''.$this->getExpression().'\', '.
              "'".$this->getName().' => ["'. $this->getType(). '", "'.$filters.'"]\'';
        }

        return '\''.$this->getExpression().'\', '.
              "'".$this->getName().' => "'. $this->getType(). '"\'';
   }

    /**
     * get expression
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * set expression
     *
     * @param string
     */
    public function setExpression($expression)
    {
        $this->_expression = $expression;
    }

    /**
     * get Name(Key)
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * set name
     *
     * @param string
     */
    public function setName($name)
    {
        $this->_name = trim($name);
    }

    /**
     * get arrayFlag
     *
     * @return boolean
     */
    public function getArrayFlag()
    {
        return $this->_arrayFlag;
    }

    /**
     * set arrayFlag
     *
     * @param boolean
     */
    public function setArrayFlag($arrayFlag)
    {
        $this->_arrayFlag = $arrayFlag;
    }

    /**
     * get type(of value)
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * set type
     *
     * @param mixed string|Diggin_Scraper_Process_Aggregate
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * get filters
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * set filters
     *
     * @param mixed
     */
    public function setFilters($filters)
    {
        $this->_filters = $filters;
    }
}

