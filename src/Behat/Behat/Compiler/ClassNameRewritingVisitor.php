<?php

namespace Behat\Behat\Compiler;

class ClassNameRewritingVisitor extends \PHPParser_NodeVisitorAbstract
{
    private $prefix;
    private $prefixesToRewrite;
    private $classNames;
    private $currentNamespace;

    public function __construct($prefix, array $prefixesToRewrite)
    {
        $this->prefix = $prefix;
        $this->prefixesToRewrite = $prefixesToRewrite;
    }

    public function reset()
    {
        $this->classNames = array();
        $this->currentNamespace = '';
    }

    public function getClassNames()
    {
        return $this->classNames;
    }

    public function enterNode(\PHPParser_Node $node)
    {
        switch (true) {
            case $node instanceof \PHPParser_Node_Stmt_Namespace:
                if (null === $node->name) {
                    $this->currentNamespace = '';
                } else {
                    $this->currentNamespace = $this->rewriteName(implode("\\", $node->name->parts));
                }

                break;

            case $node instanceof \PHPParser_Node_Stmt_Trait:
            case $node instanceof \PHPParser_Node_Stmt_Interface:
            case $node instanceof \PHPParser_Node_Stmt_Class:
                if ('' === $this->currentNamespace) {
                    $node->name = $this->rewriteName($node->name);
                }

                $this->classNames[] = empty($this->currentNamespace) ? $node->name : ($this->currentNamespace.'\\'.$node->name);
                break;

            case $node instanceof \PHPParser_Node_Scalar_String:
                if ($this->shouldBeRewritten($node->value)) {
                    $node->value = $this->prefix.$node->value;
                }
                break;

            case $node instanceof \PHPParser_Node_Name:
                $this->rewriteNameNode($node);
                break;
        }
    }

    private function rewriteNameNode(\PHPParser_Node_Name $node)
    {
        if ($node->hasAttribute('visited')) {
            return;
        }
        $node->setAttribute('visited', true);

        $newName = $this->rewriteName(implode("\\", $node->parts));
        $node->parts = explode("\\", $newName);
    }

    private function rewriteName($name)
    {
        if ( ! $this->shouldBeRewritten($name)) {
            return $name;
        }

        // If the name has no namespace, we do not add one as it would require
        // more complicated to the source code. Instead, we simply rewrite the
        // name using the PEAR-style naming convention.
        if (false === strpos($name, '\\')) {
            return str_replace('\\', '_', $this->prefix).str_replace('\\', '_', $name);
        }

        return $this->prefix.$name;
    }

    private function shouldBeRewritten($name)
    {
        foreach ($this->prefixesToRewrite as $prefix)
        {
            if (0 === strpos($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}