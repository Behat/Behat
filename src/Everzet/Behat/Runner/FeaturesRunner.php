<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Features runner.
 * Runs all initialized features runners.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesRunner extends BaseRunner implements RunnerInterface
{
    /**
     * Creates runner instance
     *
     * @param   string      $featureFiles   files/file path
     * @param   Container   $container      dependency container
     */
    public function __construct($featureFiles, Container $container)
    {
        if (!($featureFiles instanceof Finder)) {
            $featureFiles = array($featureFiles);
        }

        foreach ($featureFiles as $file) {
            $this->addChildRunner(new FeatureRunner(
                $container->getParserService()->parseFile($file)
              , $container
              , $this
            ));
        }

        parent::__construct('features', $container->getEventDispatcherService());
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            $status = max($status, $runner->run());
        }

        return $status;
    }
}
