<?php

namespace Behat\Behat\Definition;

use Behat\Behat\Context\ContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step definition.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionInterface
{
    /**
     * Returns definition type (Given|When|Then).
     *
     * @return  string
     */
    function getType();

    /**
     * Runs definition callback.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context    context object
     * @param   array                                   $tokens     array of definition tokens (outline)
     *
     * @throws  Behat\Behat\Exception\BehaviorException             if step test fails
     */
    function run(ContextInterface $context, $tokens = array());
}
