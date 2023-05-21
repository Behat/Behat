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
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
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
    public function setUp(Environment $env, $spec, $skip)
    {
        $event = new BeforeFeatureTested($env, $spec);

        $this->eventDispatcher->dispatch($event, $event::BEFORE);

        $setup = $this->baseTester->setUp($env, $spec, $skip);

        $event = new AfterFeatureSetup($env, $spec, $setup);

        $this->eventDispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, $spec, $skip)
    {
        return $this->baseTester->test($env, $spec, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, $spec, $skip, TestResult $result)
    {
        $event = new BeforeFeatureTeardown($env, $spec, $result);

        $this->eventDispatcher->dispatch($event, $event::BEFORE_TEARDOWN);

        $teardown = $this->baseTester->tearDown($env, $spec, $skip, $result);

        $event = new AfterFeatureTested($env, $spec, $result, $teardown);

        $this->eventDispatcher->dispatch($event, $event::AFTER);

        return $teardown;
    }
}
