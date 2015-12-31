<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function badgeAction()
    {
        $user= $this->get('security.context')->getToken()->getUser();
        return $this->render('CoreDashboardBundle:Default:badges.html.twig',array('user'=>$user));
    }
}
