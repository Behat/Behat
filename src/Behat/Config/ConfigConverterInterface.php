<?php

declare(strict_types=1);

namespace Behat\Config;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;

interface ConfigConverterInterface
{
    public function toPhpExpr(BuilderFactory $builderFactory): Expr;
}
