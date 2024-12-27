<?php

namespace Zhortein\ElasticEntityBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ElasticRelation
{
    /**
     * @param class-string $targetClass Class of the related entity
     * @param string       $type        relation type: nested, reference, etc
     */
    public function __construct(
        public string $targetClass,
        public string $type = 'nested',
    ) {
        $this->validateType($type);
        $this->validateTargetClass($targetClass);
    }

    private function validateType(string $type): void
    {
        $validTypes = ['reference', 'nested'];
        if (!in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException("Invalid relation type: '{$type}'. Valid types are: ".implode(', ', $validTypes).'.');
        }
    }

    /**
     * @param class-string $targetClass
     */
    private function validateTargetClass(string $targetClass): void
    {
        if (!class_exists($targetClass)) {
            throw new \InvalidArgumentException("Target class '{$targetClass}' does not exist.");
        }
    }
}
