<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;

final class JUnitFormatter extends Formatter
{
    public const NAME = 'junit';

    public function __construct(...$baseOptions)
    {
        parent::__construct(name: self::NAME, settings: $baseOptions);
    }

    /**
     * @internal
     */
    public function toPhpExpr(BuilderFactory $builderFactory): Expr
    {
        return $this->toPhpExprForNamedFormatter($builderFactory);
    }
}
