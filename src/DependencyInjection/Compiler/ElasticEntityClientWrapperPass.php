<?php

namespace Zhortein\ElasticEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zhortein\ElasticEntityBundle\Client\ClientWrapper;

class ElasticEntityClientWrapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Check if the client wrapper service is defined
        if (!$container->has(ClientWrapper::class)) {
            return;
        }

        /** @var string[] $hosts */
        $hosts = $container->getParameter('zhortein_elastic_entity.hosts');

        /** @var int $retries */
        $retries = $container->getParameter('zhortein_elastic_entity.retries');

        /** @var string $CABundlePath */
        $CABundlePath = $container->getParameter('zhortein_elastic_entity.ca_bundle_path');

        /** @var bool $elasticMetaHeader */
        $elasticMetaHeader = $container->getParameter('zhortein_elastic_entity.elastic_meta_header');

        $container->getDefinition(ClientWrapper::class)
            ->setArgument(0, $hosts)
            ->setArgument(1, $retries)
            ->setArgument(2, $CABundlePath)
            ->setArgument(3, $elasticMetaHeader)
        ;

    }
}