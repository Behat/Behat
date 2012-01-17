<?php

namespace Behat\Behat\Console\Input;

use Symfony\Component\Console\Input\InputDefinition as BaseDefinition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extended InputDefinition, which supports switchers.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InputDefinition extends BaseDefinition
{
    /**
     * Returns an InputOption by name.
     *
     * @param string $name The InputOption name
     *
     * @return InputOption A InputOption object
     *
     * @api
     */
    public function getOption($name)
    {
        if ('no-' === substr($name, 0, 3)) {
            $switch = parent::getOption('[no-]'.substr($name, 3));
            $switch->setDefault(false);

            return $switch;
        }

        if (parent::hasOption('[no-]'.$name)) {
            $switch = parent::getOption('[no-]'.$name);
            $switch->setDefault(true);

            return $switch;
        }

        return parent::getOption($name);
    }

    /**
     * Returns true if an InputOption object exists by name.
     *
     * @param string $name The InputOption name
     *
     * @return Boolean true if the InputOption object exists, false otherwise
     *
     * @api
     */
    public function hasOption($name)
    {
        if (parent::hasOption($name)) {
            return true;
        }

        if ('no-' === substr($name, 0, 3) && parent::hasOption('[no-]'.substr($name, 3))) {
            return true;
        }

        if (parent::hasOption('[no-]'.$name)) {
            return true;
        }

        return false;
    }
}
