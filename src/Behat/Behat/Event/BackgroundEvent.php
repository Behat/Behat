<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\BackgroundNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundEvent extends BehatEvent
{
    private $background;
    private $result;
    private $skipped;

    /**
     * Initializes background event.
     *
     * @param BackgroundNode $background
     * @param integer        $result
     * @param Boolean        $skipped
     */
    public function __construct(BackgroundNode $background, $result = null, $skipped = false)
    {
        $this->background   = $background;
        $this->result       = $result;
        $this->skipped      = $skipped;
    }

    /**
     * Serialize class properties.
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'background' => $this->background,
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
        $this->background = $data['background'];
        $this->result = $data['result'];
        $this->skipped = $data['skipped'];
        parent::unserialize($data['parentData']);
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

    /**
     * Return background tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks whether background were skipped.
     *
     * @return Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }
}
