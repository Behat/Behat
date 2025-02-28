<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class JUnitFormatter extends Formatter
{
    public const NAME = 'junit';

    public function __construct(...$baseOptions)
    {
        parent::__construct(name: self::NAME, settings: $baseOptions);
    }

    public function toPhpExpr(): Expr
    {
        return $this->toPhpExprForNamedFormatter();
    }
}
