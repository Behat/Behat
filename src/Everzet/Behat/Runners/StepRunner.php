<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Step;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Loaders\StepsLoader;

class StepRunner
{
    protected $step;
    protected $definitions;
    protected $container;
    protected $tokens = array();
    protected $inOutline = false;
    protected $printer;

    public function __construct(Step $step, StepsLoader $definitions, Container $container)
    {
        $this->step = $step;
        $this->definitions = $definitions;
        $this->container = $container;
        $this->printer = $container->getPrinterService();
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function setIsInOutline($inOutline)
    {
        $this->inOutline = (bool) $inOutline;
    }

    public function run()
    {
        try {
            try {
                $definition = $this->definitions->findDefinition(
                    $this->step->getText($this->tokens), $this->step->getArguments()
                );
            } catch (Ambiguous $e) {
                return $this->logStep('failed', $e);
            }
        } catch (Undefined $e) {
            return $this->logStep('undefined');
        }

        try {
            try {
                $definition->run();
                return $this->logStepDefinition('passed', $definition);
            } catch (Pending $e) {
                return $this->logStepDefinition('pending', $definition);
            }
        } catch (\Exception $e) {
            return $this->logStepDefinition('failed', $definition, $e);
        }
    }

    public function skip()
    {
        try {
            try {
                $definition = $this->definitions->findDefinition($this->step, $values);
            } catch (Ambiguous $e) {
                return $this->logStep('failed', $e);
            }
        } catch (Undefined $e) {
            return $this->logStep('undefined');
        }

        return $this->logStepDefinition(
            'skipped', $this->step->getType(), $definition, $this->step->getArguments()
        );
    }

    /**
     * Calls step printer with specific step
     *
     * @param   string      $code   step status code
     * @param   Step        $step   step instance
     * @param   Exception   $e      throwed exception
     * 
     * @return  string              step status code
     */
    protected function logStep($code, \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $this->step->getType(), $this->step->getText($this->tokens), null, null,
            $this->step->getArguments(), $e
        );

        return $code;
    }

    /**
     * Calls step printer with specific step definition
     *
     * @param   string          $code           step status code
     * @param   StepDefinition  $definition     step definition instance
     * @param   Exception       $e              throwed exception
     * 
     * @return  string                          step status code
     */
    protected function logStepDefinition($code, StepDefinition $definition, \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $this->step->getType(), $definition->getMatchedText(),
            $definition->getFile(), $definition->getLine(), $this->step->getArguments(), $e
        );

        return $code;
    }
}
