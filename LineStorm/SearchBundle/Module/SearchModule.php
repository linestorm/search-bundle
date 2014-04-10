<?php

namespace LineStorm\MediaBundle\Module;

use LineStorm\CmsBundle\Module\AbstractModule;
use LineStorm\CmsBundle\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * Class SearchModule
 * @package LineStorm\MediaBundle\Module
 */
class SearchModule extends AbstractModule implements ModuleInterface
{
    protected $name = 'Search';
    protected $id = 'search';

    /**
     * Returns the navigation array
     *
     * @return array
     */
    public function getNavigation()
    {
        return array(

        );
    }

    /**
     * Thr route to load as 'home'
     *
     * @return string
     */
    public function getHome()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function addRoutes(Loader $loader)
    {
        return $loader->import('@LineStormSearchBundle/Resources/config/routing/frontend.yml', 'rest');
    }

    /**
     * @inheritdoc
     */
    public function addAdminRoutes(Loader $loader)
    {
        return $loader->import('@LineStormSearchBundle/Resources/config/routing/admin.yml', 'yaml');
    }
} 
