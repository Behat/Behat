<?php

namespace BehaviorTester;

use \Gherkin\Feature;
use \Gherkin\Background;
use \Gherkin\ScenarioOutline;
use \Gherkin\Scenario;

interface OutputLogger
{
    public function logFeature(Feature $feature, $file);
    public function logBackground(Background $background);
    public function logScenarioOutline(ScenarioOutline $scenario);
    public function logScenario(Scenario $scenario);
    public function logStep($code, $type, $definition, $file = null,
                            $line = null, \Exception $e = null);
}
