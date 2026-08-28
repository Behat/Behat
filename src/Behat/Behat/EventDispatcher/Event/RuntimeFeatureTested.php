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
use Behat\Testwork\EventDispatcher\Event\RuntimeLifecycleEvent;

/**
 * Base implementation for feature events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal Behat's own events extend this. Third parties should implement {@see FeatureTested}.
 */
abstract class RuntimeFeatureTested extends RuntimeLifecycleEvent implements FeatureTested
{
    final public function getNode(): NodeInterface
    {
        return $this->getFeature();
    }
}
