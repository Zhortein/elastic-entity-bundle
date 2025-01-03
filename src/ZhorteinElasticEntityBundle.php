<?php

namespace Zhortein\ElasticEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\ElasticEntityClientWrapperPass;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\ElasticEntityMetadataPass;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\FormTypeCompilerPass;
use Zhortein\ElasticEntityBundle\DependencyInjection\ZhorteinElasticEntityExtension;

class ZhorteinElasticEntityBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticEntityMetadataPass());
        $container->addCompilerPass(new FormTypeCompilerPass());
        $container->addCompilerPass(new ElasticEntityClientWrapperPass());
    }

    public function getContainerExtension(): ?ZhorteinElasticEntityExtension
    {
        return new ZhorteinElasticEntityExtension();
    }
}
