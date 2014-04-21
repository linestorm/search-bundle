<?php

namespace LineStorm\SearchBundle\Search;

use LineStorm\CmsBundle\Model\ModelManager;

/**
 * Class AbstractSearchProvider
 *
 * @package LineStorm\SearchBundle\Search
 */
abstract class AbstractSearchProvider implements SearchProviderInterface
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    protected $entityMappings;

    /**
     * @param array        $entityMappings
     * @param ModelManager $modelManager
     */
    function __construct(array $entityMappings, ModelManager $modelManager)
    {
        $this->entityMappings = $entityMappings;
        $this->modelManager   = $modelManager;
    }

    /**
     * Get the entity mappings
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return $this->entityMappings;
    }


} 
