<?php

namespace Behat\Behat\Definition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;

/**
 * Step definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionInterface extends CalleeInterface
{
    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType();

    /**
     * Returns regular expression.
     *
     * @return string
     */
    public function getRegex();
}
