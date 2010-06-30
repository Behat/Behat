<?php

namespace Gherkin\I18n;

class en extends \Gherkin\RegexHolder
{
    protected $feature          = 'Feature';
    protected $background       = 'Background';
    protected $scenario         = 'Scenario';
    protected $scenarioOutline  = 'Scenario Outline';
    protected $examples         = 'Examples';
    protected $stepTypes        = array('Given', 'Then', 'When', 'And', 'But');
}