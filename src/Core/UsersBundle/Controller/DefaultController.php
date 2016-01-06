<?php

namespace Core\UsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user= $this->get('security.context')->getToken()->getUser();
        if($user->isGranted('ROLE_ADMIN'))
        {
            $url = $this->generateUrl('internals');
            
        }
        elseif ($user->isGranted('ROLE_EXTERNAL'))
        {
           $url = $this->generateUrl('my_messages');
        }
        else
        {
            $url = $this->generateUrl('messages_internals');
        }
        $response = new RedirectResponse($url);
        return $response;
    }
}
