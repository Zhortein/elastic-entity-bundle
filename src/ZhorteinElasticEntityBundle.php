<?php

namespace Zhortein\ElasticEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\ElasticEntityMetadataPass;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\FormTypeCompilerPass;

class ZhorteinElasticEntityBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticEntityMetadataPass());
        $container->addCompilerPass(new FormTypeCompilerPass());
    }
}
