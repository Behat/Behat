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
 * Step transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TransformationInterface
{
    /**
     * Transforms provided argument.
     *
     * @param string           $translatedRegex
     * @param ContextInterface $context
     * @param mixed            $argument
     *
     * @return mixed
     */
    public function transform($translatedRegex, ContextInterface $context, $argument);
}
