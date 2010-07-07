<?php

namespace Gherkin;

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
