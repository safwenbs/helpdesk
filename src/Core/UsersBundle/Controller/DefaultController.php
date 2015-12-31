<?php

namespace Core\UsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Core\UsersBundle\Entity\User;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user= $this->get('security.context')->getToken()->getUser();
        return $this->render('CoreUsersBundle:Default:home.html.twig',array('user'=>$user));
    }
}
