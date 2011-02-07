<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

use Behat\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester implements NodeVisitorInterface
{
    /**
     * Service container.
     *
     * @var     ContainerInterface
     */
    protected $container;
    /**
     * Event dispatcher.
     *
     * @var     EventDispatcher
     */
    protected $dispatcher;
    /**
     * Environment.
     *
     * @var     EnvironmentInterface
     */
    protected $environment;

    /**
     * Initialize tester.
     *
     * @param   ContainerInterface  $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Set running environment.
     *
     * @param   EnvironmentInterface    $environment    environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Visit BackgroundNode & it's steps.
     *
     * @param   AbstractNode    $background background node
     *
     * @return  integer
     */
    public function visit(AbstractNode $background)
    {
        $this->dispatcher->notify(new Event($background, 'background.before'));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->get('behat.tester.step');
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($background, 'background.after', array(
            'result'    => $result,
            'skipped'   => $skip
        )));

        return $result;
    }
}
