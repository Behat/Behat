<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

use Behat\Behat\Environment\EnvironmentInterface,
    Behat\Behat\Exception\Ambiguous,
    Behat\Behat\Exception\Undefined,
    Behat\Behat\Exception\Pending;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step Tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTester implements NodeVisitorInterface
{
    const PASSED    = 0;
    const SKIPPED   = 1;
    const PENDING   = 2;
    const UNDEFINED = 3;
    const FAILED    = 4;

    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;
    /**
     * Environment.
     *
     * @var     Behat\Behat\Environment\EnvironmentInterface
     */
    protected $environment;
    /**
     * Definition dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    protected $definitions;
    /**
     * Step replace tokens.
     *
     * @var     array
     */
    protected $tokens = array();
    /**
     * Is step marked as skipped.
     *
     * @var     boolean
     */
    protected $skip = false;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->dispatcher   = $container->get('behat.event_dispatcher');
        $this->definitions  = $container->get('behat.definition_dispatcher');
    }

    /**
     * Sets run environment.
     *
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Sets step replacements for tokens.
     *
     * @param   array   $tokens     step tokens
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Marks test as skipped.
     *
     * @param   boolean $skip   skip test?
     */
    public function skip($skip = true)
    {
        $this->skip = $skip;
    }

    /**
     * Visits & tests StepNode.
     *
     * @param   Behat\Gherkin\Node\AbstractNode $step
     *
     * @return  integer
     */
    public function visit(AbstractNode $step)
    {
        $step->setTokens($this->tokens);

        $this->dispatcher->notify(new Event($step, 'step.before', array(
            'environment'   => $this->environment
        )));

        $result     = 0;
        $definition = null;
        $exception  = null;
        $snippet    = null;

        // Find proper definition
        try {
            try {
                $definition = $this->definitions->findDefinition($step);
            } catch (Ambiguous $e) {
                $result    = self::FAILED;
                $exception = $e;
            }
        } catch (Undefined $e) {
            $result   = self::UNDEFINED;
            $snippet  = $this->definitions->proposeDefinition($step);
        }

        // Run test
        if (0 === $result) {
            if (!$this->skip) {
                try {
                    try {
                        $definition->run($this->environment, $this->tokens);
                        $result = self::PASSED;
                    } catch (Pending $e) {
                        $result    = self::PENDING;
                        $exception = $e;
                    }
                } catch (\Exception $e) {
                    $result    = self::FAILED;
                    $exception = $e;
                }
            } else {
                $result = self::SKIPPED;
            }
        }

        $this->dispatcher->notify(new Event($step, 'step.after', array(
            'result'        => $result,
            'exception'     => $exception,
            'definition'    => $definition,
            'snippet'       => $snippet,
            'environment'   => $this->environment
        )));

        return $result;
    }
}
