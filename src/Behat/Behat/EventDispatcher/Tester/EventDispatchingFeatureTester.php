<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\AfterFeatureSetup;
use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTeardown;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SpecificationTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Feature tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingFeatureTester implements SpecificationTester
{
    /**
     * @var SpecificationTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SpecificationTester      $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SpecificationTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, $feature, $skip)
    {
        $event = new BeforeFeatureTested($env, $feature);
        $this->eventDispatcher->dispatch($event::BEFORE, $event);

        $setup = $this->baseTester->setUp($env, $feature, $skip);

        $event = new AfterFeatureSetup($env, $feature, $setup);
        $this->eventDispatcher->dispatch($event::AFTER_SETUP, $event);

        return $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, $feature, $skip)
    {
        return $this->baseTester->test($env, $feature, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, $feature, $skip, TestResult $result)
    {
        $event = new BeforeFeatureTeardown($env, $feature, $result);
        $this->eventDispatcher->dispatch($event::BEFORE_TEARDOWN, $event);

        $teardown = $this->baseTester->tearDown($env, $feature, $skip, $result);

        $event = new AfterFeatureTested($env, $feature, $result, $teardown);
        $this->eventDispatcher->dispatch($event::AFTER, $event);

        return $teardown;
    }
}
