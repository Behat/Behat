<?php

namespace Everzet\Behat\Definitions;

class StepDefinition
{
    protected $type;
    protected $regex;
    protected $matchedText;
    protected $callback;
    protected $file;
    protected $line;
    protected $values = array();

    public function __construct($type, $regex, $callback, $file = null, $line = null)
    {
        $this->type = $type;
        $this->regex = $regex;
        $this->callback = $callback;
        $this->file = $file;
        $this->line = $line;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function setMatchedText($text)
    {
        $this->matchedText = $text;
    }

    public function getMatchedText()
    {
        return $this->matchedText;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function addValues(array $values)
    {
        $this->values = array_merge($this->values, $values);
    }

    public function run()
    {
        call_user_func_array($this->callback, $this->values);
    }
}
