<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Call;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\ArgumentTransformation;
use Behat\Testwork\Call\RuntimeCallee;

/**
 * Token name based transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TokenNameTransformation extends RuntimeCallee implements ArgumentTransformation
{
    const PATTERN_REGEX = '/^\:\w+$/';

    /**
     * @var string
     */
    private $pattern;

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return ':' . $argumentIndex === $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransformationCall(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            array($argumentValue)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'TokenNameTransform ' . $this->getPattern();
    }
}
