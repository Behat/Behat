<?php

declare(strict_types=1);

class IncompatibleThrowableToStringMapper
{
    public static function map($thing): string
    {
        // Simulates what happens if the PHPUnit ThrowableToStringMapper class does not behave / take the types
        // that we expect
        throw new RuntimeException('Some internal problem');
    }
}
