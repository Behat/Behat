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
use Behat\Gherkin\Node\OutlineNode;

/**
 * Outline example event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleEvent extends StepCollectionEvent
{
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var integer
     */
    private $iteration;

    /**
     * Initializes outline example event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param integer              $iteration iteration number
     * @param integer              $result
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $iteration,
        $result = null
    )
    {
        parent::__construct($suite, $contexts, $result);

        $this->outline = $outline;
        $this->iteration = $iteration;
    }

    /**
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
    }

    /**
     * Returns example number on which event occurs.
     *
     * @return integer
     */
    public function getIteration()
    {
        return $this->iteration;
    }
}
