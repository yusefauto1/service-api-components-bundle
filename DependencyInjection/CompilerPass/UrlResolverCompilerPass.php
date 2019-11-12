<?php

namespace Auto1\ServiceAPIComponentsBundle\DependencyInjection\CompilerPass;

use Auto1\ServiceAPIComponentsBundle\Exception\Core\ConfigurationException;
use Auto1\ServiceAPIComponentsBundle\Service\UrlResolver\UrlResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class UrlResolverCompilerPass
 */
class UrlResolverCompilerPass implements CompilerPassInterface
{

    const RESOLVER_TAG_NAME = 'auto1.api.url_resolver';
    const METHOD_REGISTER_RESOLVER = 'registerResolver';
    const SERVICE_CHAIN_RESOLVER = 'auto1.api.url_resolver';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {

        $chainResolverDefinition = $container->getDefinition(self::SERVICE_CHAIN_RESOLVER);

        /** @var Reference $resolver */
        foreach ($this->findAndSortTaggedServices(self::RESOLVER_TAG_NAME, $container) as $resolver) {
            $definition = $container->getDefinition((string) $resolver);
            if (!is_a($definition->getClass(), UrlResolverInterface::class, true)) {
                throw new ConfigurationException(
                    sprintf('%s should be instance of %s', self::RESOLVER_TAG_NAME, UrlResolverInterface::class)
                );
            }

            $chainResolverDefinition->addMethodCall(self::METHOD_REGISTER_RESOLVER, [$resolver]);
        }


        $warmers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $warmers[$priority][] = new Reference($id);
        }

        krsort($warmers);
        $warmers = call_user_func_array('array_merge', $warmers);
        $chainResolverDefinition->addMethodCall(self::METHOD_REGISTER_RESOLVER, [$resolver]);
    }

    /**
     * @param $tagName
     * @param ContainerBuilder $container
     * @return array
     */
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = [];

        foreach ($container->findTaggedServiceIds($tagName, true) as $serviceId => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $services[$priority][] = new Reference($serviceId);
        }

        if ($services) {
            krsort($services);
            $services = array_merge(...$services);
        }

        return $services;
    }
}
