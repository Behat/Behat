<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ExampleNode;

/**
 * Outline example event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExampleEvent extends StepCollectionEvent
{
    /**
     * @var ExampleNode
     */
    private $example;

    /**
     * Initializes outline example event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ExampleNode          $example
     * @param null|integer         $status
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ExampleNode $example,
        $status = null
    )
    {
        parent::__construct($suite, $contexts, $status);

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
