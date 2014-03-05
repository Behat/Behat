<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\Tester\OutlineTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Behat event-dispatching outline tester.
 *
 * Outline tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingOutlineTester implements OutlineTester
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
     *
     * @param OutlineTester            $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(OutlineTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, OutlineNode $outline, $skip)
    {
        $event = new OutlineTested($environment, $feature, $outline);
        $this->eventDispatcher->dispatch(OutlineTested::BEFORE, $event);
        $this->baseTester->setUp($environment, $feature, $outline, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, OutlineNode $outline, $skip)
    {
        return $this->baseTester->test($environment, $feature, $outline, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip,
        TestResult $result
    ) {
        $event = new OutlineTested($environment, $feature, $outline, $result);
        $this->eventDispatcher->dispatch(OutlineTested::AFTER, $event);
        $this->baseTester->setUp($environment, $feature, $outline, $skip, $result);
    }
}
