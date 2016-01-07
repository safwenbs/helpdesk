<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DemandController extends Controller
{
    public function demandsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $demands = $em->getRepository("CoreDashboardBundle:Demand")->findAll();
        return $this->render('CoreDashboardBundle:Demand:index.html.twig',array('user'=>$user,'demands'=>$demands));
    }
    public function configureAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;
        foreach ($data as $key=>$value)
        {
            $query = $em->createQuery("UPDATE CoreDashboardBundle:Demand d
                                       SET d.nbDaysTreatement = :nb
                                       WHERE d.demandType = :type")->setParameters(array('nb'=>$value,'type'=>$key))->getResult();
        }
        
        $this->get('session')->getFlashBag()->add('editDemands', 'Demands edited successfully.');
        $url = $this->generateUrl('demands');
        $response = new RedirectResponse($url);
        return $response;
    }
}
