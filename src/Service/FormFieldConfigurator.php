<?php

namespace Zhortein\ElasticEntityBundle\Service;

class FormFieldConfigurator
{
    /**
     * Configures field options based on directives.
     *
     * @param string               $type       The field type
     * @param array<string, mixed> $directives Specific directives for the field
     *
     * @return array<string, mixed> Options for the field
     */
    public function configureFieldOptions(string $type, array $directives): array
    {
        if (array_key_exists('attr', $directives) && is_array($directives['attr'])) {
            // Example: Add a placeholder for geo_point fields
            if ('geo_point' === $type) {
                $directives['attr']['placeholder'] = 'Enter coordinates (e.g., "48.8566,2.3522")';
            }

            // Example: Add a CSS class for all elastic fields
            /** @var string $existingClasses */
            $existingClasses = $directives['attr']['class'] ?? '';
            $directives['attr']['class'] = $existingClasses.' elastic-field';
        }

        return $directives;
    }
}
