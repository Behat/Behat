<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class JUnitFormatter extends Formatter
{
    public const NAME = 'junit';

    public function __construct()
    {
        parent::__construct(name: self::NAME);
    }
}
