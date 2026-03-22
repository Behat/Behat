<?php

namespace Behat\Config\Formatter;

/**
 * @api
 */
enum ShowOutputOption: string
{
    public const OPTION_NAME = 'show_output';
    public const PARAMETER_NAME = 'showOutput';

    case Yes = 'yes';
    case No = 'no';
    case OnFail = 'on-fail';
    case InSummary = 'in-summary';
}
