<?php

declare(strict_types=1);

namespace Behat\Tests\Config\Converter;

use Behat\Config\Converter\ConfigConverterTools;
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
}
