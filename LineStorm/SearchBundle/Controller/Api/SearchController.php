<?php

namespace LineStorm\SearchBundle\Controller\Api;

use Doctrine\ORM\Query;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use LineStorm\CmsBundle\Controller\Api\AbstractApiController;
use LineStorm\SearchBundle\Search\Exception\SearchProviderNotFoundException;

/**
 * Class SearchController
 *
 * @package LineStorm\SearchBundle\Controller\Api
 */
class SearchController extends AbstractApiController implements ClassResourceInterface
{

    /**
     * Query the search providers for a term
     *
     * @param $provider
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($provider)
    {
        // get the providers
        $searchManager = $this->get('linestorm.cms.module.search_manager');

        try
        {
            $provider = $searchManager->get($provider);
            $query = $this->getRequest()->query->get('q', null);

            if(strlen($query) >= 5)
            {
                $entities = $provider->search($query, Query::HYDRATE_ARRAY);

                foreach($entities as &$entity)
                {
                    $entity['data_url'] = call_user_func_array(array($this, 'generateUrl'), $provider->getRoute($entity));
                }

                $view = View::create($entities);
            }
            else
            {
                $view = View::create(array());
            }
        }
        catch(SearchProviderNotFoundException $e)
        {
            throw $this->createNotFoundException("Unknown search provider");
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }

}
