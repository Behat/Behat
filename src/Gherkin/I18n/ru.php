<?php

namespace Gherkin\I18n;

class ru extends en
{
    protected $feature          = 'Функционал';
    protected $background       = 'Предыстория';
    protected $scenario         = 'Сценарий';
    protected $scenarioOutline  = 'План Сценария';
    protected $examples         = 'Значения';
    protected $stepTypes        = array('Допустим', 'Тогда', 'Если', 'И', 'Но');
}