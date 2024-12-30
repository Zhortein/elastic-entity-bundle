<?php

namespace Zhortein\ElasticEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zhortein\ElasticEntityBundle\Form\ElasticEntityFormType;
use Zhortein\ElasticEntityBundle\Service\FormFieldConfigurator;

class FormTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Register the configurator
        $container
            ->register('elastic_entity.form_field_configurator', FormFieldConfigurator::class);

        // Register the form type
        $container
            ->register('elastic_entity.form.type', ElasticEntityFormType::class)
            ->addArgument(new Reference('elastic_entity.form_field_configurator'))
            ->addTag('form.type');
    }
}
