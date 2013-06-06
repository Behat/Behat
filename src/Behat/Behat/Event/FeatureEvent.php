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
class FeatureEvent extends BehatEvent
{
    private $feature;
    private $result;
    private $parameters;

    /**
     * Initializes feature event.
     *
     * @param FeatureNode $feature
     * @param mixed       $parameters
     * @param integer     $result
     */
    public function __construct(FeatureNode $feature, $parameters, $result = null)
    {
        $this->feature    = $feature;
        $this->parameters = $parameters;
        $this->result     = $result;
    }

    /**
     * Serialize class properties.
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'feature' => $this->feature,
                'parameters' => $this->parameters,
                'result' => $this->result,
                'parentData' => parent::serialize(),
            )
        );
    }

    /**
     * Unserialize class properties.
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->feature = $data['feature'];
        $this->parameters = $data['parameters'];
        $this->result = $data['result'];
        parent::unserialize($data['parentData']);
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
        return $this->parameters;
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
