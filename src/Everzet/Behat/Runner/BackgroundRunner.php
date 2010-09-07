<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\BackgroundElement;

use Everzet\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background runner.
 * Runs background element tests.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundRunner extends BaseRunner implements RunnerInterface
{
    protected $background;
    protected $environment;
    protected $skip = false;

    /**
     * Creates runner instance
     *
     * @param   BackgroundElement   $background     parsed background element
     * @param   StepsLoader         $definitions    step definitions holder
     * @param   Container           $container      dependency container
     * @param   RunnerInterface     $parent         parent runner
     */
    public function __construct(BackgroundElement $background, EnvironmentInterface $environment,
                                Container $container, RunnerInterface $parent = null)
    {
        $this->background   = $background;
        $this->environment  = $environment;

        foreach ($background->getSteps() as $step) {
            $this->addChildRunner(new StepRunner($step, $environment, $container, $this));
        }

        parent::__construct('background', $container->getEventDispatcherService(), $parent);
    }

    /**
     * Returns background element
     *
     * @return  BackgroundElement
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Is this runner has skipped steps
     *
     * @return  boolean true if has
     */
    public function isSkipped()
    {
        return $this->skip;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            if (!$this->skip) {
                $code = $runner->run();
                if ($this->statusToCode('passed') !== $code) {
                    $this->skip = true;
                }
                $status = max($status, $code);
            } else {
                $code = $runner->skip();
                $status = max($status, $code);
            }
        }

        return $status;
    }
}
