<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

class OutlineTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getEventDispatcherService();
    }

    public function visit($outline)
    {
        $this->dispatcher->notify(new Event($outline, 'outline.run.before'));

        $result = 0;

        // Run subscenarios of outline based on examples
        foreach ($outline->getExamples()->getTable()->getHash() as $iteration => $tokens) {
            $this->dispatcher->notify(new Event($outline, 'outline.sub.run.before', array(
                'iteration' => $iteration
            )));

            $environment    = $this->container->getEnvironmentService();
            $itResult       = 0;
            $skip           = false;

            // Visit & test background if has one
            if ($outline->getFeature()->hasBackground()) {
                $tester = $this->container->getBackgroundTesterService();
                $tester->setEnvironment($environment);

                $bgResult = $outline->getFeature()->getBackground()->accept($tester);

                if (0 !== $bgResult) {
                    $skip = true;
                }
                $itResult = max($itResult, $bgResult);
            }

            // Visit & test steps
            foreach ($outline->getSteps() as $step) {
                $tester = $this->container->getStepTesterService();
                $tester->setEnvironment($environment);
                $tester->setTokens($tokens);
                $tester->skip($skip);

                $stResult = $step->accept($tester);

                if (0 !== $stResult) {
                    $skip = true;
                }
                $itResult = max($itResult, $stResult);
            }

            $this->dispatcher->notify(new Event($outline, 'outline.sub.run.after', array(
                'iteration' => $iteration
              , 'result'    => $itResult
              , 'skipped'   => $skip
            )));

            $result = max($result, $itResult);
        }

        $this->dispatcher->notify(new Event($outline, 'outline.run.after', array(
            'result' => $result
        )));

        return $result;
    }
}
