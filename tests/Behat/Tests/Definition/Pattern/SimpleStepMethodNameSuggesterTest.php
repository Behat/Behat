<?php

namespace Behat\Tests\Definition\Pattern;

use Behat\Behat\Definition\Pattern\SimpleStepMethodNameSuggester;
use PHPUnit\Framework\TestCase;

class SimpleStepMethodNameSuggesterTest extends TestCase
{
    public static function providerValidMethodNames(): array
    {
        return [
            // note, placeholders will have already been removed from the incoming text
            // These cases all produce names that are valid for PHP without special handling
            'empty string' => ['', SimpleStepMethodNameSuggester::DEFAULT_NAME],
            'single word' => ['wait', 'wait'],
            'single word, caps' => ['Wait', 'wait'],
            'multiple words' => ['wait for the page to load', 'waitForThePageToLoad'],
            'multiple words, mixed case' => ['wait for Brian to say Hello', 'waitForBrianToSayHello'],
            'basic utf8' => ['upload a Résumé', 'uploadARésumé'],
            'mixed UTF8' => ['I should have £', 'iShouldHave£'],
            'alphanumeric' => ['send 5 Résumés', 'send5Résumés'],
            'slashes' => ['uploaded to web/uploads/media/default/', 'uploadedToWebUploadsMediaDefault'],
            // These contain characters that are invalid in a method name and will be stripped
            'cannot start with a number' => ['2 people arrive', 'peopleArrive'],
            'cannot contain hyphens' => ['two - three people', 'twoThreePeople'],
            'cannot contain periods' => ['some. thing.', 'someThing'],
            'cannot contain UTF8 characters outside the valid range' => ['Добавить something', 'something'],
            'only extended UTF8' => ['Добавить  число',  SimpleStepMethodNameSuggester::DEFAULT_NAME],
            'only invalid characters' => ['0-2',  SimpleStepMethodNameSuggester::DEFAULT_NAME],
        ];
    }

    /**
     * @dataProvider providerValidMethodNames
     */
    public function testGeneratesValidMethodNames(string $stepText, string $expect)
    {
        $this->assertSame($expect, (new SimpleStepMethodNameSuggester())->suggest($stepText));
    }
}
