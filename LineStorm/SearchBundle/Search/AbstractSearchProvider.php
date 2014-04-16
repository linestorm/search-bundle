<?php

namespace LineStorm\SearchBundle\Search;

use LineStorm\CmsBundle\Model\ModelManager;

/**
 * Class AbstractSearchProvider
 *
 * @package LineStorm\SearchBundle\Search
 */
abstract class AbstractSearchProvider
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @inheritdoc
     */
    public function setModelManager(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }
} 
