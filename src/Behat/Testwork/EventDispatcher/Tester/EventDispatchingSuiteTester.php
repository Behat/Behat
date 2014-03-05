<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SuiteTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork event-dispatching suite tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingSuiteTester implements SuiteTester
{
    /**
     * @var SuiteTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SuiteTester              $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SuiteTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, SpecificationIterator $iterator, $skip)
    {
        $this->eventDispatcher->dispatch(SuiteTested::BEFORE, new SuiteTested($environment));
        $this->baseTester->setUp($environment, $iterator, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, SpecificationIterator $iterator, $skip = false)
    {
        return $this->baseTester->test($environment, $iterator, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $environment, SpecificationIterator $iterator, $skip, TestResult $result)
    {
        $this->eventDispatcher->dispatch(SuiteTested::AFTER, new SuiteTested($environment, $result));
        $this->baseTester->tearDown($environment, $iterator, $skip, $result);
    }
}
