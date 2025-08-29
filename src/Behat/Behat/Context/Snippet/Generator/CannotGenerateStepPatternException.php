<?php

declare(strict_types=1);

namespace Behat\Behat\Context\Snippet\Generator;

use UnexpectedValueException;

final class CannotGenerateStepPatternException extends UnexpectedValueException
{
    public function __construct(
        public readonly string $stepText,
    ) {
        parent::__construct(
            sprintf(
                'Cannot automatically generate a step pattern matching `%s`',
                $stepText,
            ),
        );
    }
}
