<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

use Everzet\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background Tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;
    protected $environment;

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
     * Set run environment.
     *
     * @param   EnvironmentInterface    $environment    environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Visit BackgroundNode & run tests against it.
     *
     * @param   Everzet\Gherkin\Node\BackgroundNode     $background     background node
     * 
     * @return  integer                                                 result
     */
    public function visit($background)
    {
        $this->dispatcher->notify(new Event($background, 'background.run.before'));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->getBehat_StepTesterService();
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($background, 'background.run.after', array(
            'result'    => $result
          , 'skipped'   => $skip
        )));

        return $result;
    }
}
