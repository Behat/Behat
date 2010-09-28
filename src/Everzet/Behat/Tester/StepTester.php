<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

use Everzet\Behat\Environment\EnvironmentInterface;
use Everzet\Behat\Exception\Ambiguous;
use Everzet\Behat\Exception\Undefined;
use Everzet\Behat\Exception\Pending;

class StepTester implements NodeVisitorInterface
{
    const PASSED    = 0;
    const SKIPPED   = 1;
    const PENDING   = 2;
    const UNDEFINED = 3;
    const FAILED    = 4;

    protected $container;
    protected $dispatcher;
    protected $definitions;
    protected $environment;
    protected $tokens = array();
    protected $skip = false;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getEventDispatcherService();
        $this->definitions  = $container->getStepsLoaderService();
    }

    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function skip($skip = true)
    {
        $this->skip = $skip;
    }

    public function visit($step)
    {
        $step->setTokens($this->tokens);

        $this->dispatcher->notify(new Event($step, 'step.run.before'));

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
                        $definition->run($this->environment);
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

        $this->dispatcher->notify(new Event($step, 'step.run.after', array(
            'result'        => $result
          , 'exception'     => $exception
          , 'definition'    => $definition
          , 'snippet'       => $snippet
        )));

        return $result;
    }
}
