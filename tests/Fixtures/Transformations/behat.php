<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('attributes'))
        ->withSuite(
            (new Suite('simple_step_argument_transformation'))
            ->withPaths(
                'features/simple_step_argument_transformation.feature'
            )
            ->withContexts(
                'SimpleTransformationAttributesContext',
                'UserAttributesContext'
            )
        )
        ->withSuite(
            (new Suite('step_argument_transformation_without_parameters'))
            ->withPaths(
                'features/step_argument_transformation_without_parameters.feature'
            )
            ->withContexts(
                'TransformationWithoutParametersAttributesContext',
                'UserAttributesContext'
            )
        )
        ->withSuite(
            (new Suite('multiple_transformations_in_one_function'))
            ->withPaths(
                'features/multiple_transformations_in_one_function.feature'
            )
            ->withContexts(
                'MultipleTransformationsInOneFunctionAttributesContext',
                'UserAttributesContext'
            )
        )
    )
    ->withProfile(
        (new Profile('annotations'))
        ->withSuite(
            (new Suite('simple_step_argument_transformation'))
            ->withPaths(
                'features/simple_step_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('table_argument_transformation'))
            ->withPaths(
                'features/table_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('row_table_argument_transformation'))
            ->withPaths(
                'features/row_table_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('table_row_argument_transformation'))
            ->withPaths(
                'features/table_row_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('table_column_argument_transformation'))
            ->withPaths(
                'features/table_column_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('whole_table_argument_transformation'))
            ->withPaths(
                'features/whole_table_argument_transformation.feature'
            )
            ->withContexts(
                'WholeTableAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('named_argument_transformation'))
            ->withPaths(
                'features/named_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('transform_different_types'))
            ->withPaths(
                'features/transform_different_types.feature'
            )
            ->withContexts(
                'MultipleTypesAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('by_type_object_transformation'))
            ->withPaths(
                'features/by_type_object_transformation.feature'
            )
            ->withContexts(
                'ByTypeAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('by_type_and_by_name_object_transformation'))
            ->withPaths(
                'features/by_type_and_by_name_object_transformation.feature'
            )
            ->withContexts(
                'ByTypeAndByNameAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('unicode_named_argument_transformation'))
            ->withPaths(
                'features/unicode_named_argument_transformation.feature'
            )
            ->withContexts(
                'TransformationAnnotationsContext',
                'UserAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('ordinal_argument_transformation'))
            ->withPaths(
                'features/ordinal_argument_transformation.feature'
            )
            ->withContexts(
                'OrdinalTransformationAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('by_type_union_transformation'))
            ->withPaths(
                'features/by_type_union_transformation.feature'
            )
            ->withContexts(
                'ByTypeUnionAnnotationsContext'
            )
        )
        ->withSuite(
            (new Suite('scalar_type_transformation'))
            ->withPaths(
                'features/scalar_type_transformation.feature'
            )
            ->withContexts(
                'ScalarTypeAnnotationsContext'
            )
        )
    )
;
