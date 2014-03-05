<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\Tester\FeatureTester;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Behat event-dispatching feature tester.
 *
 * Feature tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingFeatureTester implements FeatureTester
{
    /**
     * @var FeatureTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param FeatureTester            $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(FeatureTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, $feature, $skip)
    {
        $this->eventDispatcher->dispatch(FeatureTested::BEFORE, new FeatureTested($environment, $feature));
        $this->baseTester->setUp($environment, $feature, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, $feature, $skip)
    {
        return $this->baseTester->test($environment, $feature, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $environment, $feature, $skip, TestResult $result)
    {
        $this->eventDispatcher->dispatch(FeatureTested::AFTER, new FeatureTested($environment, $feature, $result));
        $this->baseTester->setUp($environment, $feature, $skip, $result);
    }
}
