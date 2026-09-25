<?php

use Behat\Behat\Context\Context;
use Behat\Step\DataTable;
use Behat\Step\DocString;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private string $result;

    #[When('I build a templated string:')]
    public function iBuildATemplatedString(DataTable $table, DocString $docString): void
    {
        $vars = $table->asMap();
        $tokens = array_combine(
            array_map(
                static fn ($k) => '{'.$k.'}',
                array_keys($vars)
            ),
            $vars
        );
        $this->result = strtr(
            $docString->getContent(),
            $tokens,
        );
    }

    #[Then('the result should be:')]
    public function theResultShouldBe(DocString $docString): void
    {
        Assert::assertSame($docString->getContent(), $this->result);
    }
}
