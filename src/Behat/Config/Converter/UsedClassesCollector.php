<?php

namespace Behat\Config\Converter;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class UsedClassesCollector extends NodeVisitorAbstract
{
    /**
     * @var array <string, class-string>
     */
    private array $usedClasses = [];

    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof Node\Name\FullyQualified) {
            return null;
        }
        $className = $node->toString();
        $shortName = $node->getLast();

        $this->usedClasses[$shortName] = $className;

        return new Node\Name($shortName);
    }

    /**
     * @return array <string, class-string>
     */
    public function getUsedClasses()
    {
        return $this->usedClasses;
    }
}
