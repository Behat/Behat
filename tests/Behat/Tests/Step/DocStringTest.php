<?php

namespace Behat\Tests\Step;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Step\DocString;
use PHPUnit\Framework\TestCase;

final class DocStringTest extends TestCase
{
    public function testItExposesItsContent(): void
    {
        $docString = new DocString(new PyStringNode(['hello', 'world'], 12));

        $this->assertSame("hello\nworld", $docString->getContent());
    }

    public function testItStringifiesToItsContent(): void
    {
        $docString = new DocString(new PyStringNode(['hello', 'world'], 12));

        $this->assertSame("hello\nworld", (string) $docString);
    }
}
