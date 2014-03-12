<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeInterface;

/**
 * Represents a Gherkin node based event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface GherkinNodeTested
{
    /**
     * Returns node.
     *
     * @return NodeInterface
     */
    public function getNode();
}
