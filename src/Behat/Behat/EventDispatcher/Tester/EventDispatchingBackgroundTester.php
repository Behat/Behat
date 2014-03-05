<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\Tester\BackgroundTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Behat event-dispatching background tester.
 *
 * Background tester dispatching BEFORE/AFTER events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingBackgroundTester implements BackgroundTester
{
    /**
     * @var BackgroundTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param BackgroundTester         $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(BackgroundTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, $skip)
    {
        $event = new BackgroundTested($environment, $feature, $feature->getBackground());
        $this->eventDispatcher->dispatch(BackgroundTested::BEFORE, $event);
        $this->baseTester->setUp($environment, $feature, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, $skip)
    {
        return $this->baseTester->test($environment, $feature, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $environment, FeatureNode $feature, $skip, TestResults $results)
    {
        $event = new BackgroundTested($environment, $feature, $feature->getBackground(), $results);
        $this->eventDispatcher->dispatch(BackgroundTested::AFTER, $event);
        $this->baseTester->tearDown($environment, $feature, $skip, $results);
    }
}
