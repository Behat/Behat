<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class JSONFormatter extends Formatter
{
    public const NAME = 'json';

    public function __construct(...$baseOptions)
    {
        parent::__construct(name: self::NAME, settings: $baseOptions);
    }

    /**
     * @internal
     */
    public function toPhpExpr(): Expr
    {
        return $this->toPhpExprForNamedFormatter();
    }
}
