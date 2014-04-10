<?php

namespace LineStorm\SearchBundle;

use LineStorm\CmsBundle\DependencyInjection\ContainerBuilder\DoctrineOrmCompilerPass;
use LineStorm\SearchBundle\DependencyInjection\ContainerBuilder\SearchProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LineStormSearchBundle
 *
 * @package LineStorm\SearchBundle
 */
class LineStormSearchBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // add module pass
        $container->addCompilerPass(new SearchProviderCompilerPass());

        $modelDir = realpath(__DIR__.'/Resources/config/model/doctrine');
        $mappings = array( $modelDir => 'LineStorm\SearchBundle\Model' );
        $container->addCompilerPass(DoctrineOrmCompilerPass::getMappingsPass($mappings));
    }
}
