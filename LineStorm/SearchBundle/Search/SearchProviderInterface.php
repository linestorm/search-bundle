<?php

namespace LineStorm\SearchBundle\Search;

use Doctrine\ORM\Query;
use LineStorm\CmsBundle\Model\ModelManager;

/**
 * Interface SearchProviderInterface
 *
 * @package LineStorm\SearchBundle\Search
 */
interface SearchProviderInterface
{

    /**
     * @param array        $entityMappings
     * @param ModelManager $modelManager
     */
    function __construct(array $entityMappings, ModelManager $modelManager);

    /**
     * Return the search provider type
     *
     * @return string
     */
    public function getType();

    /**
     * Return the model name
     *
     * @return string
     */
    public function getModel();

    /**
     * Gets a count of all indices
     *
     * @return int
     */
    public function getCount();

    /**
     * Get the entity mappings
     *
     * @return array
     */
    public function getEntityMappings();

    /**
     * Search the provider for a query
     *
     * @param $query
     * @param $hydration
     *
     * @return array Collection of entities
     */
    public function search($query, $hydration = Query::HYDRATE_OBJECT);

    /**
     * Index the target model
     *
     * @param null|array|object $entities If supplied, will only index this/these entities
     *
     * @return void
     */
    public function index($entities = null);

    /**
     * Remove an index from the index
     *
     * @param object $entity
     *
     * @return void
     */
    public function remove($entity);
} 
