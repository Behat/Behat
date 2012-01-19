<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Console\Input;

use Symfony\Component\Console\Input\InputOption;

/**
 * Represents a command line switch.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InputSwitch extends InputOption
{
    /**
     * Constructor.
     *
     * @param string  $name        The option name
     * @param string  $description A description text
     *
     * @api
     */
    public function __construct($name, $description = '')
    {
        if ('--' === substr($name, 0, 2)) {
            $name = substr($name, 2);
        }

        $this->name        = $name;
        $this->description = $description;

        parent::__construct($name, null, InputOption::VALUE_OPTIONAL, $description, null);
    }
}
