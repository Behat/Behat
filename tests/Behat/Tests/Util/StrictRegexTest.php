<?php

declare(strict_types=1);

namespace Behat\Tests\Util;

use Behat\Behat\Util\RegexException;
use Behat\Behat\Util\StrictRegex;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StrictRegexTest extends TestCase
{
    private string $original_backtrack_limit;

    public static function providerReplaceValidCases(): iterable
    {
        return [
            'no replacement' => [
                [
                    '/^old/',
                    'new',
                    'some thing',
                ],
                'some thing',
            ],
            'simple replacement' => [
                [
                    '/^old/',
                    'new',
                    'old thing',
                ],
                'new thing',
            ],
            'simple replacement with limit' => [
                [
                    '/[a-z]/',
                    '1',
                    'Abcdef',
                    1,
                ],
                'A1cdef',
            ],
            'simple replacement with longer limit' => [
                [
                    '/[a-z]/',
                    '1',
                    'Abcdef',
                    3,
                ],
                'A111ef',
            ],
            'array replacements' => [
                [
                    ['/^old/', '/(thing|object)/'],
                    ['new', 'item'],
                    'old things',
                ],
                'new items',
            ],
        ];
    }

    /**
     * @dataProvider providerReplaceValidCases
     */
    public function testReplaceValidCases(array $args, string $expect): void
    {
        $this->assertSame(
            $expect,
            StrictRegex::replace(...$args),
        );
    }

    public static function providerReplaceInvalidCases(): iterable
    {
        return [
            'incorrect number of replacements in array' => [
                [
                    ['/one/', '/two/'],
                    ['uhoh'],
                    'anything',
                ],
                InvalidArgumentException::class,
                'Expected 2 entries in $replacement array, got 1',
            ],
            'invalid regex pattern' => [
                [
                    '/broken',
                    'anything',
                    'anything',
                ],
                RegexException::class,
                'No ending delimiter',
            ],
            'excessive backtracking' => [
                [
                    '/(x+x+)+y/',
                    '',
                    'xxxxxxxxxxxxy',
                ],
                RegexException::class,
                'Regex failed: Backtrack limit exhausted',
            ],
        ];
    }

    /**
     * @dataProvider providerReplaceInvalidCases
     */
    public function testReplaceInvalidCases(array $args, string $expectException, string $expectMsg): void
    {
        $this->expectException($expectException);
        $this->expectExceptionMessage($expectMsg);
        StrictRegex::replace(...$args);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->original_backtrack_limit = ini_set('pcre.backtrack_limit', '2');
    }

    protected function tearDown(): void
    {
        ini_set('pcre.backtrack_limit', $this->original_backtrack_limit);
        parent::tearDown();
    }
}
