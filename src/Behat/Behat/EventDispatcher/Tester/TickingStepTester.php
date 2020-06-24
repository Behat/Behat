<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Enable ticks during step testing to allow SigintController in Testwork
 * to handle an interupt (on PHP7)
 *
 * @see Behat\Testwork\EventDispatcher\Cli\SigintController
 * 
 * @deprecated Since the way signals are handled changed to use pcntl_signal_dispatch
 *   this class is no longer needed.
 * 
 * @todo Remove this class in the next major version
 *
 * @author Peter Mitchell <peterjmit@gmail.com>
 */
final class TickingStepTester implements StepTester
{
    /**
     * @var StepTester
     */
    private $baseTester;

    /**
     * Initializes tester.
     *
     * @param StepTester  $baseTester
     */
    public function __construct(StepTester $baseTester)
    {
        $this->baseTester = $baseTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->baseTester->setUp($env, $feature, $step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        declare(ticks = 1);

        return $this->baseTester->test($env, $feature, $step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        return $this->baseTester->tearDown($env, $feature, $step, $skip, $result);
    }
}
