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
     * Set the Model Manager
     *
     * @param ModelManager $modelManager
     *
     * @return mixed
     */
    public function setModelManager(ModelManager $modelManager);

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
    public function getName();

    /**
     * Return an array of fields (as keys) and mappings (as array or fields)
     *
     * @return array
     */
    public function getIndexFields();

    /**
     * Gets a cound of all indices
     *
     * @return int
     */
    public function getCount();

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
     * @return void
     */
    public function index();
} 
