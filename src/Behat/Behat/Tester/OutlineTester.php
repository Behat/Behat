<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester implements NodeVisitorInterface
{
    /**
     * Service container.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Visits & tests OutlineNode.
     *
     * @param   Behat\Gherkin\Node\AbstractNode $outline
     *
     * @return  integer
     */
    public function visit(AbstractNode $outline)
    {
        $this->dispatcher->notify(new Event($outline, 'outline.before'));

        $result = 0;

        // Run subscenarios of outline based on examples
        foreach ($outline->getExamples()->getHash() as $iteration => $tokens) {
            $environment    = $this->container->get('behat.environment_builder')->build();
            $itResult       = 0;
            $skip           = false;

            $this->dispatcher->notify(new Event($outline, 'outline.example.before', array(
                'iteration'     => $iteration,
                'environment'   => $environment
            )));

            // Visit & test background if has one
            if ($outline->getFeature()->hasBackground()) {
                $tester = $this->container->get('behat.tester.background');
                $tester->setEnvironment($environment);

                $bgResult = $outline->getFeature()->getBackground()->accept($tester);

                if (0 !== $bgResult) {
                    $skip = true;
                }
                $itResult = max($itResult, $bgResult);
            }

            // Visit & test steps
            foreach ($outline->getSteps() as $step) {
                $tester = $this->container->get('behat.tester.step');
                $tester->setEnvironment($environment);
                $tester->setTokens($tokens);
                $tester->skip($skip);

                $stResult = $step->accept($tester);

                if (0 !== $stResult) {
                    $skip = true;
                }
                $itResult = max($itResult, $stResult);
            }

            $this->dispatcher->notify(new Event($outline, 'outline.example.after', array(
                'iteration'     => $iteration,
                'result'        => $itResult,
                'skipped'       => $skip,
                'environment'   => $environment
            )));

            $result = max($result, $itResult);
        }

        $this->dispatcher->notify(new Event($outline, 'outline.after', array(
            'result' => $result
        )));

        return $result;
    }
}
