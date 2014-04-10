<?php

namespace LineStorm\SearchBundle\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SearchProviderCompilerPass
 *
 * @package LineStorm\SearchBundle\DependencyInjection\ContainerBuilder
 */
class SearchProviderCompilerPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('linestorm.cms.module.search_manager'))
        {
            return;
        }

        $definition = $container->getDefinition('linestorm.cms.module.search_manager');
        $taggedServices = $container->findTaggedServiceIds('linestorm.cms.module.search.provider');

        foreach ($taggedServices as $id => $attributes)
        {
            $definition->addMethodCall(
                'addSearchProvider',
                array(new Reference($id))
            );

            $provider = $container->getDefinition($id);
            $provider->addMethodCall(
                'setModelManager',
                array(new Reference('linestorm.cms.model_manager'))
            );
        }
    }
} 
