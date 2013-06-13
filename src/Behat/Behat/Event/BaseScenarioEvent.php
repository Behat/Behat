<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Context\ContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class BaseScenarioEvent extends BehatEvent
{
    private $context;
    private $result;
    private $skipped;

    /**
     * Initializes scenario event.
     *
     * @param ContextInterface $context
     * @param integer          $result
     * @param Boolean          $skipped
     */
    public function __construct(ContextInterface $context, $result = null, $skipped = false)
    {
        $this->context = $context;
        $this->result  = $result;
        $this->skipped = $skipped;
    }

    /**
     * Serialize class properties.
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'context' => $this->context,
                'result' => $this->result,
                'skipped' => $this->skipped,
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
        $this->context = $data['context'];
        $this->result = $data['result'];
        $this->skipped = $data['skipped'];
        parent::unserialize($data['parentData']);
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
     * Returns scenario tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks whether scenario were skipped.
     *
     * @return Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }
}
