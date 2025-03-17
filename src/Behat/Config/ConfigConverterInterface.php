<?php

declare(strict_types=1);

namespace Behat\Config;

use PhpParser\Node\Expr;

/**
 * @internal
 */
interface ConfigConverterInterface
{
    /**
     * @internal
     */
    public function toPhpExpr(): Expr;
}
