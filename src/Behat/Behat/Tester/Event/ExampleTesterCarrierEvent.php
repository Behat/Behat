<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ContextualTesterCarrierEvent;
use Behat\Gherkin\Node\ExampleNode;

/**
 * Outline example tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExampleTesterCarrierEvent extends ContextualTesterCarrierEvent
{
    /**
     * @var ExampleNode
     */
    private $example;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ExampleNode          $example
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, ExampleNode $example)
    {
        parent::__construct($suite, $contexts);

        $this->example = $example;
    }

    /**
     * Returns example node.
     *
     * @return ExampleNode
     */
    public function getExample()
    {
        return $this->example;
    }
}
