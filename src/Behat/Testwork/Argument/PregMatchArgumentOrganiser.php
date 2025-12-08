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
     * Initialises organiser.
     */
    public function __construct(
        private readonly ArgumentOrganiser $baseOrganiser,
    ) {
    }

    public function organiseArguments(ReflectionFunctionAbstract $function, array $arguments)
    {
        $cleanedArguments = $this->cleanupMatchDuplicates($arguments);

        return $this->baseOrganiser->organiseArguments($function, $cleanedArguments);
    }

    /**
     * Cleans up provided preg_match match into a list of arguments.
     *
     * `preg_match` matches named arguments with named indexes and also
     * represents all arguments with numbered indexes. This method removes
     * duplication and also drops the first full match element from the
     * array.
     *
     * @return list<mixed>
     */
    private function cleanupMatchDuplicates(array $match): array
    {
        $cleanMatch = array_slice($match, 1);
        $arguments = [];

        $keys = array_keys($cleanMatch);
        $numKeys = count($keys);
        for ($keyIndex = 0; $keyIndex < $numKeys; ++$keyIndex) {
            $key = $keys[$keyIndex];

            $arguments[$key] = $cleanMatch[$key];

            if ($this->isKeyAStringAndNextOneIsAnInteger($keyIndex, $keys)) {
                ++$keyIndex;
            }
        }

        return $arguments;
    }

    /**
     * Checks if key at provided index is a string and next key in the array is an integer.
     *
     * @param int     $keyIndex
     * @param mixed[] $keys
     */
    private function isKeyAStringAndNextOneIsAnInteger($keyIndex, array $keys): bool
    {
        $keyIsAString = is_string($keys[$keyIndex]);
        $nextKeyIsAnInteger = isset($keys[$keyIndex + 1]) && is_int($keys[$keyIndex + 1]);

        return $keyIsAString && $nextKeyIsAnInteger;
    }
}
