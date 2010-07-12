<?php

namespace Everzet\Gherkin\I18n;

use \Everzet\Gherkin\RegexHolder;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class en extends RegexHolder
{
    protected $feature          = 'Feature';
    protected $background       = 'Background';
    protected $scenario         = 'Scenario';
    protected $scenarioOutline  = 'Scenario Outline';
    protected $examples         = 'Examples';
    protected $stepTypes        = array('Given', 'Then', 'When', 'And', 'But');
}