<?php

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