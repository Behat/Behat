<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Environment\EnvironmentInterface;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleEvent extends OutlineEvent implements EventInterface
{
    private $iteration;
    private $environment;
    private $skipped;

    /**
     * Initializes outline example event.
     *
     * @param   Behat\Gherkin\Node\OutlineNode                  $outline
     * @param   integer                                         $iteration  number of iteration
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     * @param   integer                                         $result
     * @param   Boolean                                         $skipped
     */
    public function __construct(OutlineNode $outline, $iteration, EnvironmentInterface $environment,
                                $result = null, $skipped = false)
    {
        parent::__construct($outline, $result);

        $this->iteration    = $iteration;
        $this->environment  = $environment;
        $this->skipped      = $skipped;
    }

    /**
     * Returns example number on which event occurs.
     *
     * @return  integer
     */
    public function getIteration()
    {
        return $this->iteration;
    }

    /**
     * Returns environment object.
     *
     * @return  Behat\Behat\Environment\EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks whether outline example were skipped.
     *
     * @return  Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }
}
