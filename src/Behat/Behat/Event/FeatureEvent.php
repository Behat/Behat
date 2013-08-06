<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureEvent extends Event implements EventInterface
{
    private $feature;
    private $result;
    private $contextParameters;

    /**
     * Initializes feature event.
     *
     * @param FeatureNode $feature
     * @param mixed       $contextParameters
     * @param integer     $result
     */
    public function __construct(FeatureNode $feature, $contextParameters, $result = null)
    {
        $this->feature           = $feature;
        $this->contextParameters = $contextParameters;
        $this->result            = $result;
    }

    /**
     * Returns feature node.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns context parameters.
     *
     * @return mixed
     */
    public function getContextParameters()
    {
        return $this->contextParameters;
    }

    /**
     * Returns feature tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }
}
