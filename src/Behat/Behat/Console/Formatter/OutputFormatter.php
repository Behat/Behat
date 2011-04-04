<?php

namespace Behat\Behat\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter as BaseOutputFormatter,
    Symfony\Component\Console\Formatter\OutputFormatterStyle;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console output formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputFormatter extends BaseOutputFormatter
{
    /**
     * {@inheritdoc}
     */
    public function __construct($decorated = null, array $styles = array())
    {
        parent::__construct($decorated, array_merge(array(
            'undefined'     => new OutputFormatterStyle('yellow'),
            'pending'       => new OutputFormatterStyle('yellow'),
            'pending_param' => new OutputFormatterStyle('yellow', null, array('bold')),
            'failed'        => new OutputFormatterStyle('red'),
            'failed_param'  => new OutputFormatterStyle('red', null, array('bold')),
            'passed'        => new OutputFormatterStyle('green'),
            'passed_param'  => new OutputFormatterStyle('green', null, array('bold')),
            'skipped'       => new OutputFormatterStyle('cyan'),
            'skipped_param' => new OutputFormatterStyle('cyan', null, array('bold')),
            'comment'       => new OutputFormatterStyle('black'),
            'tag'           => new OutputFormatterStyle('cyan')
        ), $styles));
    }

    /**
     * {@inheritdoc}
     */
    protected function getBeginStyleRegex()
    {
        return '#{\+([a-z][a-z0-9\-_=;]+)}#i';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEndStyleRegex()
    {
        return '#{\-([a-z][a-z0-9\-_]*)?}#i';
    }
}
