<?php

namespace Everzet\Behat\Exceptions;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Undefined extends BehaviorException
{
    protected $step;

    public function __construct($step)
    {
        $this->step = $step;

        parent::__construct();
    }

    public function __toString()
    {
        return sprintf('Undefined step "%s"', $this->step);
    }
}