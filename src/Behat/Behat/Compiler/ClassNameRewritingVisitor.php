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
                $this->rewriteName($node->name);
                $this->currentNamespace = $node->name ? implode("\\", $node->name->parts) : '';
                break;

            case $node instanceof \PHPParser_Node_Stmt_Trait:
            case $node instanceof \PHPParser_Node_Stmt_Interface:
            case $node instanceof \PHPParser_Node_Stmt_Class:
                $this->classNames[] = empty($this->currentNamespace) ? $node->name : ($this->currentNamespace.'\\'.$node->name);
                break;

            case $node instanceof \PHPParser_Node_Scalar_String:
                if ($this->shouldBeRewritten($node->value)) {
                    $node->value = $this->prefix.$node->value;
                }
                break;

            case $node instanceof \PHPParser_Node_Name:
                $this->rewriteName($node);
                break;
        }
    }

    private function rewriteName(\PHPParser_Node_Name $node)
    {
        if ($node->hasAttribute('visited')) {
            return;
        }
        $node->setAttribute('visited', true);

        $name = implode("\\", $node->parts);
        if ( ! $this->shouldBeRewritten($name)) {
            return;
        }

        $newParts = explode("\\", $this->prefix.$name);
        $node->parts = $newParts;
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