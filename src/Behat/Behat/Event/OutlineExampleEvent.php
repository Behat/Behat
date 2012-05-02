<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Context\ContextInterface;

use Behat\Gherkin\Node\OutlineNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline example event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleEvent extends OutlineEvent implements EventInterface
{
    private $iteration;
    private $context;
    private $skipped;

    /**
     * Initializes outline example event.
     *
     * @param OutlineNode      $outline
     * @param integer          $iteration iteration number
     * @param ContextInterface $context
     * @param integer          $result
     * @param Boolean          $skipped
     */
    public function __construct(OutlineNode $outline, $iteration, ContextInterface $context,
                                $result = null, $skipped = false)
    {
        parent::__construct($outline, $result);

        $this->iteration = $iteration;
        $this->context   = $context;
        $this->skipped   = $skipped;
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

    /**
     * Returns context object.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Checks whether outline example were skipped.
     *
     * @return Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }
}
