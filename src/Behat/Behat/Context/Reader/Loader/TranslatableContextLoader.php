<?php

namespace Behat\Behat\Context\Reader\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\TranslatableContextInterface;
use Behat\Behat\Suite\SuiteInterface;
use InvalidArgumentException;
use Symfony\Component\Translation\Translator;

/**
 * Translatable context loader.
 * Loads translation resources from translatable contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TranslatableContextLoader implements LoaderInterface
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Initializes loader.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Loads translation resources from context implementing TranslatableContextInterface.
     *
     * @param SuiteInterface $suite
     * @param string         $contextClass
     *
     * @return array
     *
     * @throws InvalidArgumentException If getTranslationResources() returns unsupported resource type
     *
     * @see TranslatableContextInterface
     */
    public function loadCallees(SuiteInterface $suite, $contextClass)
    {
        if (!is_subclass_of($contextClass, 'Behat\Behat\Context\TranslatableContextInterface')) {
            return array();
        }

        foreach (call_user_func(array($contextClass, 'getTranslationResources')) as $path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            switch ($extension) {
                case 'yml':
                    $this->addResource('yaml', $path, basename($path, '.' . $extension), $suite);
                    break;
                case 'xliff':
                    $this->addResource('xliff', $path, basename($path, '.' . $extension), $suite);
                    break;
                case 'php':
                    $this->addResource('php', $path, basename($path, '.' . $extension), $suite);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf(
                        'Can not read definition translations from file "%s". File type is not supported.',
                        $path
                    ));
            }
        }

        return array();
    }

    /**
     * Adds resource to translator instance.
     *
     * @param string         $type
     * @param string         $path
     * @param string         $language
     * @param SuiteInterface $suite
     */
    private function addResource($type, $path, $language, SuiteInterface $suite)
    {
        $this->translator->addResource($type, $path, $language, $suite->getId());
    }
}
