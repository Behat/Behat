<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Everzet\Gherkin;

class Step
{
    protected $type;
    protected $text;
    protected $arguments = array();

    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getText(array $tokens = array())
    {
        $text = $this->text;

        foreach ($tokens as $key => $value)
        {
          $text = str_replace('<'.$key.'>', $value, $text, $count);
        }

        return $text;
    }

    public function addArgument($argument)
    {
        $this->arguments[] = $argument;
    }

    public function hasArguments()
    {
        return count($this->arguments) > 0;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}
