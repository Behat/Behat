<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\OutlineNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineEvent extends Event implements EventInterface
{
    private $outline;
    private $result;

    /**
     * Initializes outline event.
     *
     * @param OutlineNode $outline
     * @param integer     $result
     */
    public function __construct(OutlineNode $outline, $result = null)
    {
        $this->outline  = $outline;
        $this->result   = $result;
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
     * Returns outline tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }
}
