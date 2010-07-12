<?php

namespace Everzet\Behat\Printers;

use \Everzet\Gherkin\Feature;
use \Everzet\Gherkin\Background;
use \Everzet\Gherkin\ScenarioOutline;
use \Everzet\Gherkin\Scenario;

interface Printer
{
    public function logFeature(Feature $feature, $file);
    public function logBackground(Background $background);
    public function logScenarioOutline(ScenarioOutline $scenario);
    public function logScenario(Scenario $scenario);
    public function logStep($code, $type, $text, $file = null,
                            $line = null, \Exception $e = null);
}
