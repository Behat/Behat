<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\AfterOutlineSetup;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTeardown;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\Tester\OutlineTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Outline tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingOutlineTester implements OutlineTester
{
    /**
     * @var OutlineTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     */
    public function __construct(OutlineTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setUp(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip)
    {
        $event = new BeforeOutlineTested($env, $feature, $outline);

        $this->eventDispatcher->dispatch($event, $event::BEFORE);

        $setup = $this->baseTester->setUp($env, $feature, $outline, $skip);

        $event = new AfterOutlineSetup($env, $feature, $outline, $setup);

        $this->eventDispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    public function test(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip)
    {
        return $this->baseTester->test($env, $feature, $outline, $skip);
    }

    public function tearDown(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip, TestResult $result)
    {
        $event = new BeforeOutlineTeardown($env, $feature, $outline, $result);

        $this->eventDispatcher->dispatch($event, $event::BEFORE_TEARDOWN);

        $teardown = $this->baseTester->tearDown($env, $feature, $outline, $skip, $result);

        $event = new AfterOutlineTested($env, $feature, $outline, $result, $teardown);

        $this->eventDispatcher->dispatch($event, $event::AFTER);

        return $teardown;
    }
}
