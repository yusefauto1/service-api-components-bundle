<?php

namespace Auto1\ServiceAPIComponentsBundle\DependencyInjection\CompilerPass;

use Auto1\ServiceAPIComponentsBundle\Exception\Core\ConfigurationException;
use Auto1\ServiceAPIComponentsBundle\Service\UrlResolver\UrlResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
//use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class UrlResolverCompilerPass
 */
class UrlResolverCompilerPass implements CompilerPassInterface
{
//    use PriorityTaggedServiceTrait;

    const RESOLVER_TAG_NAME = 'auto1.api.url_resolver';
    const METHOD_REGISTER_RESOLVER = 'registerResolver';
    const SERVICE_CHAIN_RESOLVER = 'auto1.api.url_resolver';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $warmers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $warmers[$priority][] = new Reference($id);
        }

        krsort($warmers);
        $warmers = call_user_func_array('array_merge', $warmers);
    }
}
