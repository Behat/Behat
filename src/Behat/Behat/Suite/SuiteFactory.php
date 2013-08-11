<?php

namespace Behat\Behat\Suite;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\Generator\GeneratorInterface;
use RuntimeException;

/**
 * Suite factory.
 * Creates suite instances from name, type & parameters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteFactory
{
    /**
     * @var GeneratorInterface[]
     */
    private $generators = array();

    /**
     * Registers suite generator.
     *
     * @param GeneratorInterface $generator
     */
    public function registerGenerator(GeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * Creates suite from provided name, type & parameters.
     *
     * @param string $name
     * @param string $type
     * @param array  $parameters
     *
     * @return SuiteInterface
     *
     * @throws RuntimeException If no appropriate generator found
     */
    public function createSuite($name, $type, array $parameters)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($type, $parameters)) {
                return $generator->generate($name, $parameters);
            }
        }

        throw new RuntimeException(sprintf(
            'Can not find suite generator for suite "%s" of type "%s".', $name, $type
        ));
    }
}
