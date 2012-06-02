<?php

namespace Behat\Behat\Definition;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Exception\BehaviorException;

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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionInterface
{
    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType();

    /**
     * Runs definition callback.
     *
     * @param ContextInterface $context
     *
     * @return mixed
     *
     * @throws BehaviorException
     */
    public function run(ContextInterface $context);
}
