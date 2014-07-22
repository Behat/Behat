<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use ReflectionFunctionAbstract;

/**
 * Organises arguments coming from preg_match results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PregMatchArgumentOrganiser implements ArgumentOrganiser
{
    /**
     * @var ArgumentOrganiser
     */
    private $baseOrganiser;

    /**
     * Initialises organiser.
     *
     * @param ArgumentOrganiser $organiser
     */
    public function __construct(ArgumentOrganiser $organiser)
    {
        $this->baseOrganiser = $organiser;
    }

    /**
     * {@inheritdoc}
     */
    public function organiseArguments(ReflectionFunctionAbstract $function, array $match)
    {
        $arguments = $this->cleanupMatchDuplicates($match);

        return $this->baseOrganiser->organiseArguments($function, $arguments);
    }

    /**
     * Cleans up provided preg_match match into a list of arguments.
     *
     * `preg_match` matches named arguments with named indexes and also
     * represents all arguments with numbered indexes. This method removes
     * duplication and also drops the first full match element from the
     * array.
     *
     * @param array $match
     *
     * @return mixed[]
     */
    private function cleanupMatchDuplicates(array $match)
    {
        $cleanMatch = array_slice($match, 1);
        $arguments = array();

        $keys = array_keys($cleanMatch);
        for ($keyIndex = 0; $keyIndex < count($keys); $keyIndex++) {
            $key = $keys[$keyIndex];

            $arguments[$key] = $cleanMatch[$key];

            if ($this->isKeyAStringAndNexOneIsAnInteger($keyIndex, $keys)) {
                $keyIndex += 1;
            }
        }

        return $arguments;
    }

    /**
     * Checks if key at provided index is a string and next key in the array is an integer.
     *
     * @param integer $keyIndex
     * @param mixed[] $keys
     *
     * @return Boolean
     */
    private function isKeyAStringAndNexOneIsAnInteger($keyIndex, array $keys)
    {
        $keyIsAString = is_string($keys[$keyIndex]);
        $nextKeyIsAnInteger = isset($keys[$keyIndex + 1]) && is_integer($keys[$keyIndex + 1]);

        return $keyIsAString && $nextKeyIsAnInteger;
    }
}
