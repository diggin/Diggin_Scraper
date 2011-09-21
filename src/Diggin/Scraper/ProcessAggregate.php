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

use Diggin\Scraper\Process,
    IteratorAggregate;

class ProcessAggregate implements IteratorAggregate
{

    protected $_processes = array();

    public function getProcesses() 
    {
        return $this->_processes;
    }
    
    public function getIterator() 
    {
        return new \ArrayIterator($this->_processes);
    }
    
    /**
     * setting process like DSL of Web::Scraper
     * Sample&Demo is : demos/Diggin/Scraper/
     * 
     * @params mixed args1, args2, args3,,,
     * $thisObejct->process('expresssion', 'key => val, filter, filter,', 'key => val)
     * like
     * $thisObejct->process('//div[@class="post-content"]/ul/li/a', 'title => TEXT')
     * 
     * expression : (depend on)Strategy
     *  [Default] Css Or Xpath 
     * key : results's key. 
     *  $scraper->results['key'];
     *  can access as this class's property (by __get method)
     *  $scraper->key
     * val : (depend on)Strategy
     * filter :
     *  filtering by 
     *  user_func , Zend_Filter_*, Your_Filter_*(implements Zend_Filter_Interface)
     * 
     * @see Diggin_Scraper_Filter
     * @return Diggin_Scraper_Process_Aggregate Provides a fluent interface
     */
    public function process($args)
    {

        $args = func_get_args();
        
        if ($args[0] instanceof Process) {
            $this->_processes[] = $args[0];
            return $this;
        }

        $expression = array_shift($args);
 
        foreach ($args as $nametype) {
            if(is_string($nametype)) {
                if (strpos($nametype, '=>') !== false) {
                    list($name, $types) = explode('=>', $nametype);
                } else {
                    throw new Exception("invalid argument. none with \'->\'");
                }

                if ((substr(trim($name), -2) == '[]')) {
                    $name = substr(trim($name), 0, -2);
                    $arrayflag = true;
                } else {
                    $arrayflag = false;
                }

                $types = trim($types, " '\"");

                //types to array
                if (strpos($types, ',') !== false) $types = explode(',', $types);

                //filter exists?
                if (count($types) === 1) {
                    //none filter

                    $process = new Process;
                    $process->setExpression($expression);
                    $process->setName(trim($name));
                    $process->setArrayflag($arrayflag);
                    $process->setType($types);
                    $process->setFilters(false);

                } else {
                    //filters
                    foreach ($types as $count => $type) {
                        if ($count !== 0) $filters[] = trim($type, " []'\"");
                    }

                    $process = new Process;
                    $process->setExpression($expression);
                    $process->setName(trim($name));
                    $process->setArrayflag($arrayflag);
                    $process->setType(trim($types[0], " []'\""));
                    $process->setFilters($filters);
                }

                $this->_processes[] = $process;
            } elseif (is_array($nametype)) {
                if(!is_int(key($nametype))) {

                    foreach ($nametype as $name => $nm) {
                        if ((substr($name, -2) == '[]')) {
                            $name = substr($name, 0, -2);
                            $arrayflag = true;
                        } else {
                            $arrayflag = false;
                        }
                        $process = new Process();
                        $process->setExpression($expression);
                        $process->setName($name);
                        $process->setArrayFlag($arrayflag);
                        $process->setType($nm);
                        $process->setFilters(false);

                        $this->_processes[] = $process;
                    }
                } else {
                    $process = new Process();
                    $process->setExpression($expression);
                    $process->setName($nametype[0]);
                    $process->setArrayflag($nametype[1]);
                    $process->setType($nametype[2]);
                    $process->setFilters((is_array($nametype[3]) ? $nametype[3] : false));
                    $this->_processes[] = $process;
                }
            }
        }

        return $this;
    }
}
