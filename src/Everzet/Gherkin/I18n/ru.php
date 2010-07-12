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

class ru extends RegexHolder
{
    protected $feature          = 'Функционал';
    protected $background       = 'Предыстория';
    protected $scenario         = 'Сценарий';
    protected $scenarioOutline  = 'Структура сценария';
    protected $examples         = 'Значения';
    protected $stepTypes        = array('Допустим', 'То', 'Если', 'И', 'Но');
}