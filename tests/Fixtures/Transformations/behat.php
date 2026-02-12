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
                'UserContext'
            )
        )
        ->withSuite(
            (new Suite('step_argument_transformation_without_parameters'))
            ->withPaths(
                'features/step_argument_transformation_without_parameters.feature'
            )
            ->withContexts(
                'TransformationWithoutParametersAttributesContext',
                'UserContext'
            )
        )
        ->withSuite(
            (new Suite('multiple_transformations_in_one_function'))
            ->withPaths(
                'features/multiple_transformations_in_one_function.feature'
            )
            ->withContexts(
                'MultipleTransformationsInOneFunctionAttributesContext',
                'UserContext'
            )
        )
        ->withSuite(
            (new Suite('table_column_argument_transformation'))
                ->withPaths(
                    'features/table_column_argument_transformation.feature'
                )
                ->withContexts(
                    'ColumnTransformationContext',
                    'UserContext'
                )
        )
    )
;
