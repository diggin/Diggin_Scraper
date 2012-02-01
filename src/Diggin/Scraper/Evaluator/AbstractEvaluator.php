<?php

namespace Diggin\Scraper\Evaluator;

use ArrayIterator,
    Diggin\Scraper\Process;

abstract class AbstractEvaluator extends ArrayIterator
{
    private $_process;

    public function __construct(array $values, Process $process)
    {
        $this->_process = $process;

        return parent::__construct($values);
    }

    public function getProcess()
    {
        return $this->_process;
    }

    public function current()
    {
        return $this->_eval(parent::current());
    }

    abstract protected function _eval($value);
}

