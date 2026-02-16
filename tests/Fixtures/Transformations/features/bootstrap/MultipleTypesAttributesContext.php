<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;

class MultipleTypesAttributesContext implements Context
{
    private mixed $value;

    #[Transform('/^".*"$/')]
    public function transformString(string $string): string
    {
        return $string;
    }

    #[Transform(':number workdays ago')]
    public function transformDate(string $number): DateTime
    {
        return new DateTime("-$number days");
    }

    #[Transform('/^\d+$/')]
    public function transformInt(string $int): int
    {
        return intval($int);
    }

    #[Transform('/^null/')]
    public function transformNull()
    {
        return null;
    }

    #[Given('I have the value ":value"')]
    public function iHaveTheValue(mixed $value): void
    {
        $this->value = $value;
    }

    #[Then('it should be of type :type')]
    public function itShouldBeOfType(string $type)
    {
        if (gettype($this->value) != $type && get_class($this->value) != $type) {
            throw new Exception('Expected ' . $type . ', got ' . gettype($this->value) . ' (value: ' . var_export($this->value, true) . ')');
        }
    }
}
