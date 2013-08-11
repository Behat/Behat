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
use Behat\Gherkin\Node\OutlineNode;

/**
 * Outline example tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleTesterCarrierEvent extends ContextualTesterCarrierEvent
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
     * @var array
     */
    private $tokens;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param integer              $iteration
     * @param array                $tokens
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $iteration,
        array $tokens
    )
    {
        parent::__construct($suite, $contexts);

        $this->outline = $outline;
        $this->iteration = $iteration;
        $this->tokens = $tokens;
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
     * Returns outline example iteration number.
     *
     * @return integer
     */
    public function getIteration()
    {
        return $this->iteration;
    }

    /**
     * Returns example row tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
