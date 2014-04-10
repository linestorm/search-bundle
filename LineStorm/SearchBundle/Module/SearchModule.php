<?php

namespace LineStorm\SearchBundle\Module;

use LineStorm\CmsBundle\Module\AbstractModule;
use LineStorm\CmsBundle\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class SearchModule
 *
 * @package LineStorm\SearchBundle\Module
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
            'View Providers' => array('linestorm_cms_admin_module_search_list', array())
        );
    }

    /**
     * The route to load as 'home'
     *
     * @return string
     */
    public function getHome()
    {
        return 'linestorm_cms_admin_module_search_list';
    }

    /**
     * @inheritdoc
     */
    public function addRoutes(Loader $loader)
    {
        return new RouteCollection();
        //return $loader->import('@LineStormSearchBundle/Resources/config/routing/frontend.yml', 'rest');
    }

    /**
     * @inheritdoc
     */
    public function addAdminRoutes(Loader $loader)
    {
        return $loader->import('@LineStormSearchBundle/Resources/config/routing/admin.yml', 'yaml');
    }
} 
