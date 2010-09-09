<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\StepElement;

use Everzet\Behat\Exception\Ambiguous;
use Everzet\Behat\Exception\Undefined;
use Everzet\Behat\Exception\Pending;
use Everzet\Behat\Definition\StepDefinition;
use Everzet\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step runner.
 * Runs step tests.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepRunner extends BaseRunner implements RunnerInterface
{
    protected $step;
    protected $environment;
    protected $definitions;
    protected $definition;

    protected $snippet;
    protected $exception;

    /**
     * Creates runner instance
     *
     * @param   StepElement             $step           step element
     * @param   EnvironmentInterface    $environment    runners environment
     * @param   Container               $container      dependency container
     * @param   RunnerInterface         $parent         parent runner
     */
    public function __construct(StepElement $step, EnvironmentInterface $environment,
                                Container $container, RunnerInterface $parent)
    {
        $this->step         = $step;
        $this->environment  = $environment;
        $this->definitions  = $container->getStepsLoaderService();

        parent::__construct('step', $container->getEventDispatcherService(), $parent);
    }

    /**
     * Set step tokens (replace values for <item1>/<item2>)
     *
     * @param   array   $tokens associative array of tokens
     */
    public function setTokens(array $tokens)
    {
        $this->step->setTokens($tokens);
    }

    /**
     * Returns step element
     *
     * @return  StepElement
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns exception
     *
     * @return  BehaviorException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Returns step definition
     *
     * @return  StepDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Returns step definition snippet
     *
     * @return  array   md5_key => definition
     */
    public function getDefinitionSnippet()
    {
        return $this->snippet;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getStepsCount()
    {
        return 1;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getStepsStatusesCount()
    {
        return array($this->getStatus() => 1);
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getFailedStepRunners()
    {
        return $this->statusToCode('failed') === $this->statusCode ? array($this) : array();
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getPendingStepRunners()
    {
        return $this->statusToCode('pending') === $this->statusCode 
          ? array(md5($this->definition->getFile() . $this->definition->getLine()) => $this)
          : array();
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getDefinitionSnippets()
    {
        return is_array($this->snippet) ? $this->snippet : array();
    }

    /**
     * Find definition for current step from defintions holder
     */
    protected function findDefinition()
    {
        try {
            try {
                $this->definition = $this->definitions->findDefinition($this->step);
            } catch (Ambiguous $e) {
                $this->statusCode   = $this->statusToCode('failed');
                $this->exception    = $e;
            }
        } catch (Undefined $e) {
            $this->statusCode   = $this->statusToCode('undefined');
            $this->snippet      = $this->definitions->proposeDefinition($this->step);
        }
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $this->findDefinition();

        if (0 === $this->statusCode) {
            try {
                try {
                    $this->definition->run($this->environment);
                    $this->statusCode = $this->statusToCode('passed');
                } catch (Pending $e) {
                    $this->statusCode   = $this->statusToCode('pending');
                    $this->exception    = $e;
                }
            } catch (\Exception $e) {
                $this->statusCode   = $this->statusToCode('failed');
                $this->exception    = $e;
            }
        }

        return $this->statusCode;
    }

    /**
     * Skips current step test
     *
     * @return  integer status code
     */
    public function skip()
    {
        $this->fireEvent('skip.before');

        $this->findDefinition();

        if (0 === $this->statusCode) {
            $this->statusCode = $this->statusToCode('skipped');
        }

        $this->fireEvent('skip.after');

        return $this->statusCode;
    }
}
