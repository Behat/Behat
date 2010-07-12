<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Everzet\Gherkin\I18n;

use \Everzet\Gherkin\RegexHolder;

class en extends RegexHolder
{
    protected $feature          = 'Feature';
    protected $background       = 'Background';
    protected $scenario         = 'Scenario';
    protected $scenarioOutline  = 'Scenario Outline';
    protected $examples         = 'Examples';
    protected $stepTypes        = array('Given', 'Then', 'When', 'And', 'But');
}