<?php

namespace LineStorm\SearchBundle\Search;

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
     * Search the provider for a query
     *
     * @param $query
     *
     * @return array Collection of entities
     */
    public function search($query);

    /**
     * Index the target model
     *
     * @return void
     */
    public function index();
} 
