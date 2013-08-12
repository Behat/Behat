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
use Behat\Behat\Tester\Event\ContextualTesterCarrierEvent;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\BackgroundNode;

/**
 * Background tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTesterCarrierEvent extends ContextualTesterCarrierEvent
{
    /**
     * @var BackgroundNode
     */
    private $background;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param BackgroundNode       $background
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        BackgroundNode $background
    ) {
        parent::__construct($suite, $contexts);

        $this->background = $background;
    }

    /**
     * Returns background node.
     *
     * @return BackgroundNode
     */
    public function getBackground()
    {
        return $this->background;
    }
}
