<?php

namespace Everzet\Behat\Console;

use \Symfony\Component\Console\Application as BaseApplication;

use \Everzet\Behat\Console\Command\BehatCommand;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console application.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends BaseApplication
{
    /**
     * @see \Symfony\Component\Console\Application
     */
    public function __construct()
    {
        parent::__construct('BehaviorTester', '0.1');

        $this->addCommands(array(
            new BehatCommand()
        ));
    }
}
