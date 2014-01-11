<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\OutlineTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatching outline tester.
 *
 * Outline tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DispatchingOutlineTester extends OutlineTester
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Sets event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function testOutline(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip = false
    ) {
        $this->eventDispatcher && $this->eventDispatcher->dispatch(
            OutlineTested::BEFORE,
            new OutlineTested($suite, $environment, $feature, $outline)
        );

        $result = parent::testOutline($suite, $environment, $feature, $outline, $skip);

        $this->eventDispatcher && $this->eventDispatcher->dispatch(
            OutlineTested::AFTER,
            new OutlineTested($suite, $environment, $feature, $outline, $result)
        );

        return $result;
    }
}
