<?php

declare(strict_types=1);

namespace Behat\Tests\Config\Converter;

use Behat\Config\Converter\ConfigConverterTools;
use Behat\Config\TesterOptions;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

final class ConfigConverterToolsTest extends TestCase
{
    public static function providerRemoveDefaultArguments(): array
    {
        $object = new class {
            public function takesNoArgs(): void
            {

            }

            public function withSomething(bool $something): void
            {

            }

            public function withSingleDefault(bool $value = true): void
            {

            }

            public function withVariadic(string ...$args): void
            {

            }

            public function withMultipleArgs(string $first, string $second = 'b', string $third = 'c'): void
            {

            }
        };

        return [
            'no args, nothing removed' => [
                $object::class,
                'takesNoArgs',
                [],
                '(new %CLASS%())->takesNoArgs()',
            ],
            'no default, value set false' => [
                $object::class,
                'withSomething',
                [false],
                '(new %CLASS%())->withSomething(false)',
            ],
            'no default, value set true' => [
                $object::class,
                'withSomething',
                [true],
                '(new %CLASS%())->withSomething(true)',
            ],
            'has single default, non-default value' => [
                $object::class,
                'withSingleDefault',
                [false],
                '(new %CLASS%())->withSingleDefault(false)',
            ],
            'has single default, default value' => [
                $object::class,
                'withSingleDefault',
                [true],
                '(new %CLASS%())->withSingleDefault()',
            ],
            'variadic is unchanged' => [
                $object::class,
                'withVariadic',
                ['a', 'b', 'c'],
                "(new %CLASS%())->withVariadic('a', 'b', 'c')",
            ],
            'multiple args, all non-default' => [
                $object::class,
                'withMultipleArgs',
                ['1', '2', '3'],
                "(new %CLASS%())->withMultipleArgs('1', '2', '3')",
            ],
            'multiple args, all are default' => [
                $object::class,
                'withMultipleArgs',
                ['1', 'b', 'c'],
                "(new %CLASS%())->withMultipleArgs('1')",
            ],
            'multiple args, last is default' => [
                $object::class,
                'withMultipleArgs',
                ['1', '2', 'c'],
                "(new %CLASS%())->withMultipleArgs('1', '2')",
            ],
            'multiple args, middle is default (change to named)' => [
                $object::class,
                'withMultipleArgs',
                ['1', 'b', '3'],
                "(new %CLASS%())->withMultipleArgs('1', third: '3')",
            ],
            'provided as named params' => [
                $object::class,
                'withSomething',
                ['something' => true],
                "(new %CLASS%())->withSomething(something: true)",
            ],
        ];
    }

    /**
     * @dataProvider providerRemoveDefaultArguments
     */
    public function testCanRemoveDefaultArgumentsFromMethodCalls(string $class, string $method, array $args, string $expect): void
    {
        $expr = ConfigConverterTools::createObject($class);
        $expr = ConfigConverterTools::addMethodCall($class, $method, $args, $expr);
        $printer = new Standard();
        $code = str_replace('\\' . $class, '%CLASS%', $printer->prettyPrintExpr($expr));
        $this->assertSame($expect, $code);
    }

    public static function providerValidateMethodCallInput(): array
    {
        $obj = new class {
            public function withThings(string $one, string $two): void
            {

            }
        };

        return [
            'undefined class' => [
                'This\Is\Not\A\Class',
                'whatever',
                [],
                'Class "This\Is\Not\A\Class" does not exist',
            ],
            'undefined method' => [
                $obj::class,
                'withNothing',
                [],
                'Method class@anonymous::withNothing() does not exist',
            ],
            'not enough params' => [
                $obj::class,
                'withThings',
                ['one'],
                'Missing argument #1 (two)',
            ],
            'too many params' => [
                $obj::class,
                'withThings',
                ['one', 'two', 'three'],
                'Too many arguments provided for',
            ],
        ];
    }

    /**
     * @dataProvider providerValidateMethodCallInput
     */
    public function testAddMethodCallValidatesInput(string $class, string $method, array $args, string $expect): void
    {
        $expr = ConfigConverterTools::createObject($class);
        $this->expectExceptionMessage($expect);
        ConfigConverterTools::addMethodCall($class, $method, $args, $expr);
    }

    public static function providerErrorReportingToConstants(): array
    {
        $OLD_E_STRICT = 2048;
        $E_ALL_WITH_STRICT = E_ALL | $OLD_E_STRICT;

        return [
            [E_ALL, 'E_ALL'],
            [0, '0'],
            [E_ALL & ~E_DEPRECATED, 'E_ALL & ~E_DEPRECATED'],
            [E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED), 'E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED)'],
            [
                E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_WARNING),
                'E_ALL & ~(E_WARNING | E_DEPRECATED | E_USER_DEPRECATED)',
            ],
            [
                E_NOTICE,
                'E_NOTICE',
            ],
            [
                E_WARNING | E_NOTICE,
                'E_WARNING | E_NOTICE',
            ],
            [
                E_WARNING | E_NOTICE | E_DEPRECATED,
                'E_WARNING | E_NOTICE | E_DEPRECATED',
            ],
            [
                // Their config value includes bits that are not defined as PHP error levels.
                E_WARNING | 65536,
                'E_WARNING | 65536',
            ],
            [
                // We can safely ignore the old E_STRICT value
                E_ALL & ~$OLD_E_STRICT,
                'E_ALL',
            ],
            [
                // We can safely ignore the old E_STRICT value
                $E_ALL_WITH_STRICT,
                'E_ALL',
            ],
            [
                // We can safely ignore the old E_STRICT value
                $E_ALL_WITH_STRICT & ~E_DEPRECATED,
                'E_ALL & ~E_DEPRECATED',
            ],
            [
                // We can safely ignore the old E_STRICT value
                $OLD_E_STRICT | E_WARNING,
                'E_WARNING',
            ],
            [
                // I guess users can still use environment vars for this...?
                '%env(BEHAT_ERR_REPORTING)%',
                "'%env(BEHAT_ERR_REPORTING)%'",
            ],
        ];
    }

    /**
     * @dataProvider providerErrorReportingToConstants
     */
    public function testErrorReportingToConstants(mixed $reporting, string $expect): void
    {
        // Can be used with any method, but might as well test with the one that has the expected signature
        $expr = ConfigConverterTools::createObject(TesterOptions::class);
        $expr = ConfigConverterTools::addMethodCall(
            TesterOptions::class,
            'withErrorReporting',
            [ConfigConverterTools::errorReportingToConstants($reporting)],
            $expr,
        );

        $printer = new Standard();
        $this->assertSame(
            '(new \\' . TesterOptions::class . '())->withErrorReporting(' . $expect . ')',
            $printer->prettyPrintExpr($expr),
        );
    }
}
