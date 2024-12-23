<?php

namespace Behat\Config\Formatter;

enum ShowOutputOption: string
{
    public const OPTION_NAME = 'show_output';

    case Yes = 'yes';
    case No = 'no';
    case OnFail = 'on-fail';
    case InSummary = 'in-summary';
}
