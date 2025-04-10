<?php

/*
 * Used by the `class_references.yaml` file to define the class names that will be converted to PHP class references.
 */
use Behat\Behat\Context\Context;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreateClassNamesExtension implements Extension
{
    public function process(ContainerBuilder $container)
    {
    }

    public function getConfigKey()
    {
        return 'anything';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
    }

}

// These are the classes that are referenced from the class_references YAML.
//
// They need to be actually defined for the reference to pass the `class_exists()` check we
// use to identify PHP classes from other strings.
//
// Avoid the verbosity that would be required to define them all separately (especially considering they
// are all in different namespaces) by defining them with class_alias.
//
// The other complication is that because the feature runs with the YAML copied to a temp directory,
// it is tricky to configure the Behat autoloader to find these classes in the "fixtures" directory.
// Hence using an ugly extension file to run this code.
class_alias(CreateClassNamesExtension::class, 'Some\Behat\Extension\ExplicitlyReferencedExtension');
class_alias(CreateClassNamesExtension::class, 'Some\ShorthandExtension\ServiceContainer\ShorthandExtension');
$context = new class implements Context {
};
class_alias($context::class, 'MyContext');
class_alias($context::class, 'test\MyApp\Contexts\MyFirstContext');
class_alias($context::class, 'test\MyApp\Contexts\MySecondContext');

return new CreateClassNamesExtension();
