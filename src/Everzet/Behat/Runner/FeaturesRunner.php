<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

class FeaturesRunner extends BaseRunner implements RunnerInterface
{
    public function __construct(Finder $featureFiles, Container $container)
    {
        foreach ($featureFiles as $file) {
            $this->addChildRunner(new FeatureRunner(
                $container->getParserService()->parseFile($file)
              , $container
              , $this
            ));
        }

        parent::__construct('suite', $container->getEventDispatcherService());
    }

    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            $status = max($status, $runner->run());
        }

        return $status;
    }
}
