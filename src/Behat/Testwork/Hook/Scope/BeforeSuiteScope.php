<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Represents a scope for BeforeSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteScope implements SuiteScope
{
    /**
     * Initializes scope.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly SpecificationIterator $iterator,
    ) {
    }

    public function getName(): string
    {
        return self::BEFORE;
    }

    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getSpecificationIterator(): SpecificationIterator
    {
        return $this->iterator;
    }
}
