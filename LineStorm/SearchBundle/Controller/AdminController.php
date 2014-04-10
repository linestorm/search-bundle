<?php

namespace LineStorm\SearchBundle\Controller;

use FOS\RestBundle\View\View;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AdminController
 *
 * @package LineStorm\SearchBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * List all the providers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function listAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $searchManager = $this->get('linestorm.cms.module.search_manager');

        $providers = $searchManager->getSearchProviders();

        return $this->render('LineStormSearchBundle:Admin:list.html.twig', array(
            'providers' => $providers,
        ));
    }

}
