<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline Tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;

    /**
     * Initialize tester.
     *
     * @param   Container   $container  injection container
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getBehat_EventDispatcherService();
    }

    /**
     * Visit OutlineNode & run tests against it.
     *
     * @param   Everzet\Gherkin\Node\OutlineNode        $outline        outline node
     * 
     * @return  integer                                                 result
     */
    public function visit($outline)
    {
        $this->dispatcher->notify(new Event($outline, 'outline.run.before'));

        $result = 0;

        // Run subscenarios of outline based on examples
        foreach ($outline->getExamples()->getTable()->getHash() as $iteration => $tokens) {
            $this->dispatcher->notify(new Event($outline, 'outline.sub.run.before', array(
                'iteration' => $iteration
            )));

            $environment    = $this->container->getBehat_EnvironmentService();
            $itResult       = 0;
            $skip           = false;

            // Visit & test background if has one
            if ($outline->getFeature()->hasBackground()) {
                $tester = $this->container->getBehat_BackgroundTesterService();
                $tester->setEnvironment($environment);

                $bgResult = $outline->getFeature()->getBackground()->accept($tester);

                if (0 !== $bgResult) {
                    $skip = true;
                }
                $itResult = max($itResult, $bgResult);
            }

            // Visit & test steps
            foreach ($outline->getSteps() as $step) {
                $tester = $this->container->getBehat_StepTesterService();
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
