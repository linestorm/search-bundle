<?php

namespace LineStorm\SearchBundle\Search;

use LineStorm\SearchBundle\Search\Exception\SearchProviderNotFoundException;

/**
 * Class SearchRepositoryManager
 *
 * @package LineStorm\SearchBundle\Search
 */
class SearchRepositoryManager
{

    /**
     * This holds all the mappings of model -> SearchProvider
     *
     * @var SearchProviderInterface[]
     */
    protected $searchProviders = array();


    /**
     * @param SearchProviderInterface $searchProvider
     */
    public function addSearchProvider(SearchProviderInterface $searchProvider)
    {
        $this->searchProviders[$searchProvider->getModel()] = $searchProvider;
    }

    /**
     * @return SearchProviderInterface[]
     */
    public function getSearchProviders()
    {
        return $this->searchProviders;
    }


    /**
     * Given an entity name and returns the search provider for that entity
     *
     * @param string $repoName
     *
     * @return SearchProviderInterface
     * @throws Exception\SearchProviderNotFoundException
     */
    public function get($repoName)
    {
        if (!array_key_exists($repoName, $this->searchProviders))
        {
            throw new SearchProviderNotFoundException($repoName);
        }

        return $this->searchProviders[$repoName];
    }

    /**
     * Trigger a full index on one or all providers
     *
     * @param string|null $repoName
     */
    public function index($repoName = null)
    {
        if (array_key_exists($repoName, $this->searchProviders))
        {
            $this->searchProviders[$repoName]->index();
        }
        else
        {
            foreach ($this->searchProviders as $provider)
            {
                $provider->index();
            }
        }
    }
} 
