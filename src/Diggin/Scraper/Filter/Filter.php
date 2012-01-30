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
namespace Diggin\Scraper\Filter;

use Diggin\Scraper\Exception;

class Filter extends \IteratorIterator
{
    /**
     * Filter - store
     *
     * @var mixed
     */
    private $_filter = array();

    /**
     * Factory (called via Diggin_Scraper_Callback_Filter)
     *
     * @param Iterator $iterator
     * @param mixed $filter
     * @return Iterator
     */
    public static function factory(\Iterator $iterator, $filter)
    {
        if ( ($filter instanceof \Zend\Filter\Filter) or 
              (is_string($filter) and (preg_match('/^[0-9a-zA-Z]/', $filter)) or
              is_callable($filter)) ) {
            $iterator = new static($iterator);
            $iterator->setFilter($filter);
        } else {
            $prefix = $filter[0];

            if ($prefix === '/' or $prefix === '#') {
                $iterator = new \RegexIterator($iterator, $filter);
            } elseif ($prefix === '$') {
                $iterator = new \RegexIterator($iterator, $filter);
                $iterator->setMode(\RegexIterator::GET_MATCH);
            } else {
                throw new Exception\RuntimeException("Unable to load filter '$filter': {$e->getMessage()}");
            }
        }

        return $iterator;
    }

    public function setFilter($filter)
    {
        //user-func or lambda
        if (is_callable($filter)) {
            $this->_filter = $filter;
        } else {
            if (class_exists($filter)) {
                try {
                    $filter = new $filter();
                } catch (\Zend\Filter\Exception $e) {
                    throw new Exception\RuntimeException("Unable to load filter '$filter': {$e->getMessage()}");
                }
            }
            if (!$filter instanceof \Zend\Filter\Filter) {
                $className = get_class($filter);
                throw new Exception("Unable to load filter: $className");
            }

            $this->_filter['filter'] = $filter;
        }
    }

    /**
     * Override method & callback filter
     */
    public function current()
    {
        return call_user_func(is_array($this->_filter) ? 
                                array(current($this->_filter), key($this->_filter)) : 
                                $this->_filter, 
                              parent::current());
    }
}
