<?php

namespace Behat\Behat\Definition\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Exception\RedundantException;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Definitions carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionsCarrierEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var DefinitionInterface[]
     */
    private $definitions = array();

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
    }

    /**
     * Returns suite.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Adds definition to carrier.
     *
     * @param DefinitionInterface $definition
     *
     * @throws RedundantException If definition with same regex already exists
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $regex = $definition->getRegex();

        if (isset($this->definitions[$regex])) {
            throw new RedundantException($definition, $this->definitions[$regex]);
        }

        $this->definitions[$regex] = $definition;
    }

    /**
     * Returns all added definitions.
     *
     * @return DefinitionInterface[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}
