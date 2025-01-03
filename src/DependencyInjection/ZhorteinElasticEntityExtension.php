<?php

namespace Zhortein\ElasticEntityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ZhorteinElasticEntityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        /**
         * @var array{
         *     hosts?: string[],
         *     retries?: int,
         *     CABundlePath?: string,
         *     elasticMetaHeader?: bool
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('zhortein_elastic_entity.hosts', $config['hosts'] ?? Configuration::DEFAULT_HOSTS);
        $container->setParameter('zhortein_elastic_entity.retries', $config['retries'] ?? Configuration::DEFAULT_RETRIES);
        $container->setParameter('zhortein_elastic_entity.ca_bundle_path', $config['ca_bundle_path'] ?? Configuration::DEFAULT_CA_BUNDLE_PATH);
        $container->setParameter('zhortein_elastic_entity.elastic_meta_header', $config['elastic_meta_header'] ?? Configuration::DEFAULT_ELASTIC_META_HEADER);
    }
}
