<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gherkin\I18n;

class ru extends \Gherkin\RegexHolder
{
    protected $feature          = 'Функционал';
    protected $background       = 'Предыстория';
    protected $scenario         = 'Сценарий';
    protected $scenarioOutline  = 'Структура сценария';
    protected $examples         = 'Значения';
    protected $stepTypes        = array('Допустим', 'То', 'Если', 'И', 'Но');
}