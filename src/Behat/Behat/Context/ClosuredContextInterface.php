<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Closured context interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ClosuredContextInterface extends ContextInterface
{
    /**
     * Returns array of step definition files (*.php).
     *
     * @return array
     */
    public function getStepDefinitionResources();

    /**
     * Returns array of hook definition files (*.php).
     *
     * @return array
     */
    public function getHookDefinitionResources();
}
