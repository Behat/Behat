<?php

namespace Behat\Behat\Context\UseCase;

use Behat\Behat\Context\Generator\GeneratorInterface;
use Behat\Behat\Suite\SuiteInterface;
use RuntimeException;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context class generator.
 * Generates context class code using registerd generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GenerateContextClass
{
    /**
     * @var GeneratorInterface[]
     */
    private $generators = array();

    /**
     * Registers context generator.
     *
     * @param GeneratorInterface $generator
     */
    public function registerGenerator(GeneratorInterface $generator)
    {
        $this->generators[] = $generator;

        usort($this->generators, function (GeneratorInterface $generator1, GeneratorInterface $generator2) {
            return $generator2->getPriority() - $generator1->getPriority();
        });
    }

    /**
     * Returns feature context skelet.
     *
     * @param SuiteInterface $suite
     * @param string         $classname
     *
     * @return string
     *
     * @throws RuntimeException If appropriate generator is not found
     */
    public function generateContextClass(SuiteInterface $suite, $classname)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($suite, $classname)) {
                return $generator->generate($suite, $classname);
            }
        }

        throw new RuntimeException(sprintf(
            'Could not find context generator for "%s" class of the "%s" suite.',
            $classname,
            $suite->getName()
        ));
    }
}
