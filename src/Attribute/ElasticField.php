<?php

namespace Zhortein\ElasticEntityBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ElasticField
{
    /**
     * @param string               $type       Type of the field (e.g., text, keyword, integer, etc.).
     * @param bool                 $nullable   Indicate if the field is nullable or not
     * @param string|null          $analyzer   analyzer for text fields
     * @param array<string, mixed> $directives Additional directives for the field (e.g., ignore, dynamic settings).
     */
    public function __construct(
        public string $type = 'text',
        public bool $nullable = false,
        public ?string $analyzer = null,
        public array $directives = [],
    ) {
        $this->validateType($type);
        $this->validateAnalyzer($analyzer);
    }

    private function validateType(string $type): void
    {
        $validTypes = ['text', 'keyword', 'integer', 'float', 'date', 'boolean'];
        if (!in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException("Invalid type: '{$type}'. Valid types are: ".implode(', ', $validTypes).'.');
        }
    }

    private function validateAnalyzer(?string $analyzer): void
    {
        if (null !== $analyzer && !preg_match('/^[a-zA-Z0-9_]+$/', $analyzer)) {
            throw new \InvalidArgumentException("Invalid analyzer name: '{$analyzer}'. Analyzer names must be alphanumeric.");
        }
    }
}
