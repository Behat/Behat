<?php

namespace Behat\Behat\Suite\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\SuiteInterface;

/**
 * Generator interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * Checks if generator support provided suite type and parameters.
     *
     * @param string $type
     * @param array  $parameters
     *
     * @return Boolean
     */
    public function supports($type, array $parameters);

    /**
     * Generate suite with provided name and parameters.
     *
     * @param string $suiteName
     * @param array  $parameters
     *
     * @return SuiteInterface
     */
    public function generate($suiteName, array $parameters);
}
