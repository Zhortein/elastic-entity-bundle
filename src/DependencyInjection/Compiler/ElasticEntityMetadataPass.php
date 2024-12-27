<?php

namespace Zhortein\ElasticEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;

class ElasticEntityMetadataPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Check if the metadata collector service is defined
        if (!$container->has(MetadataCollector::class)) {
            return;
        }

        $metadataCollectorDefinition = $container->findDefinition(MetadataCollector::class);

        // Retrieve all service definitions
        foreach ($container->getDefinitions() as $definition) {
            $className = $definition->getClass();

            if ($className && class_exists($className)) {
                $reflectionClass = new \ReflectionClass($className);

                if ($this->isElasticEntity($reflectionClass)) {
                    $metadataCollectorDefinition->addMethodCall('addMetadata', [$reflectionClass]);
                }
            }
        }
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function isElasticEntity(\ReflectionClass $reflectionClass): bool
    {
        foreach ($reflectionClass->getAttributes() as $attribute) {
            if (ElasticEntity::class === $attribute->getName()) {
                return true;
            }
        }

        return false;
    }
}
