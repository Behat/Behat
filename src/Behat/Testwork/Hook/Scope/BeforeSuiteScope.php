<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Context\SuiteContext;

/**
 * Represents a scope for BeforeSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteScope implements SuiteScope
{
    /**
     * @var SpecificationIterator
     */
    private $iterator;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes scope.
     *
     * @param SuiteContext $context
     */
    public function __construct(SuiteContext $context)
    {
        $this->iterator = $context->getSpecificationIterator();
        $this->environment = $context->getEnvironment();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::BEFORE;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificationIterator()
    {
        return $this->iterator;
    }
}
