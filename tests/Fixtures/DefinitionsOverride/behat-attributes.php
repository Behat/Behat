<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('inherit'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextInheritAttributes'],
        ]))
    )
    ->withProfile((new Profile('both_patterns'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextBothAttributes'],
        ]))
    )
    ->withProfile((new Profile('parent_attribute_child_annotation'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextMixedAttributeParentAnnotationChild'],
        ]))
    )
    ->withProfile((new Profile('parent_annotation_child_attribute'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextMixedAnnotationParentAttributeChild'],
        ]))
    );
