<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Core\DashboardBundle\Entity\Message;

class MessageController extends Controller
{
    public function writeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $demands = $em->getRepository("CoreDashboardBundle:Demand")->findAll();
        
        return $this->render('CoreDashboardBundle:Message:new.html.twig',array('user'=>$user,'demands'=>$demands));
    }
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $data = $request->request;
        
        $demandID = $data->get('demandType');
        $demand = $em->getRepository("CoreDashboardBundle:Demand")->find($demandID);
        $contactType = $data->get('contactType');
        $subject = $data->get('subject');
        $body = $data->get('message');
        
        $code = $this->generateRandomString(15);
        $message  = new Message();
        $message->setContactType($contactType);
        $message->setCreatedBy($user);
        $message->setDemandType($demand);
        $message->setMessage($body);
        $message->setSubject($subject);
        $message->setCode($code);
        
        $em->persist($message);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('newMessage', 'Message sent to reception successfully , your request will be treated shortly , you will receive a response in few days !! ');
        $url = $this->generateUrl('my_messages');
        $response = new RedirectResponse($url);
        return $response;
    }
    public function listAction(Request $request)
    {
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $myMessages = $user->getMessages();
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($myMessages, $request->query->getInt('page', 1),10);
        return $this->render('CoreDashboardBundle:Message:list.html.twig',array('user'=>$user,'messages'=>$pagination));
    }
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        return $this->render('CoreDashboardBundle:Message:view.html.twig',array('user'=>$user,'message'=>$message));
    }
    
    public function internalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->isGranted('ROLE_INTERNAL_RECEPTION')) {
            $service = 'Reception';
        } elseif ($user->isGranted('ROLE_INTERNAL_ACCOUNTING')) {
            $service = 'Accounting';
        } elseif ($user->isGranted('ROLE_INTERNAL_ADMINISTRATION')) {
            $service = 'Administration';
        } else {
            $service = 'It';
        }
        if($user->getIsBoss())
        {
          $messages = $em->createQuery("SELECT m FROM CoreDashboardBundle:Message m
                                        WHERE m.currentService =:service
                                        AND m.status NOT IN ('closed','archived')
                                        ORDER BY m.createdOn DESC")->setParameter("service",$service);
        }
        else
        {
            $messages = $em->createQuery("SELECT m FROM CoreDashboardBundle:Message m
                                          WHERE m.currentService =:service
                                          AND m.status NOT IN ('closed','archived')
                                          AND m.canBeViewed = 1
                                          ORDER BY m.createdOn DESC")->setParameter("service",$service);
            
        }
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($messages, $request->query->getInt('page', 1),10);
        
        return $this->render('CoreDashboardBundle:Message:inbox_internal.html.twig',array('user'=>$user,'messages'=>$pagination));
    }
    public function treatAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        if(!$message->getIsViewed())
        {
            $message->setViewedBy($user);
        }
        $message->setIsViewed(TRUE);
        $message->setStatus("ongoing");
        $em->persist($message);
        $em->flush();
        return $this->render('CoreDashboardBundle:Message:treat.html.twig',array('user'=>$user,'message'=>$message));
    }
    public function moveAction($id,$service)
    {
        $em = $this->getDoctrine()->getManager();
        
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        $message->setCurrentService($service);
        $message->setCanBeViewed(FALSE);
        $em->persist($message);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('moveRequest', 'Request moved to '.$service.' service successfully !! ');
        $url = $this->generateUrl('messages_internals');
        $response = new RedirectResponse($url);
        return $response;
    }
    public function shareAction($id,$share)
    {
        $em = $this->getDoctrine()->getManager();
        
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        $message->setCanBeViewed($share);
        $em->persist($message);
        $em->flush();
        if ($share)
        {
            $notice = "Request shared with other dependence users successfully.";
        }
        else
        {
            $notice = "The sharing of this request has been disabled with other dependence users successfully.";
        }
        $this->get('session')->getFlashBag()->add('shareRequest', $notice);
        $url = $this->generateUrl('messages_internals');
        $response = new RedirectResponse($url);
        return $response;
    }
    public function closeAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $today = new \DateTime('now');
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        $data = $request->request;
        $responseText = $data->get('responseText');
        $message->setIsTreated(TRUE);
        $message->setStatus('closed');
        $message->setClosedOn($today);
        $message->setResponseText($responseText);
        $message->setClosedBy($user);
        
        $em->persist($message);
        $em->flush();
        
        //SEND EMAIL TO THE EXTERNAL
//        $mail = \Swift_Message::newInstance()
//                 ->setSubject("Your request has been treated and closed ")
//                 ->setFrom('safwen.bensalem@mapp-net.com')
//                 ->setTo($message->getCreatedBy()->getEmail())
//                 ->setContentType("text/html")
//                 ->setBody('CoreDashboardBundle:Message:mail.html.twig',array('message'=>$message))
//                 ;
//        $this->get('mailer')->send($mail);
        
        $this->get('session')->getFlashBag()->add('closeRequest', 'Request closed successfully , the response has been sent to the external user !! ');
        $url = $this->generateUrl('messages_internals');
        $response = new RedirectResponse($url);
        return $response;
        
    }
    
    public function typeAction(Request $request, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $messages = $em->createQuery("SELECT m FROM CoreDashboardBundle:Message m
                                      WHERE m.status =:status
                                      ORDER BY m.createdOn DESC")->setParameter("status",$type);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($messages, $request->query->getInt('page', 1),10);
        
        return $this->render('CoreDashboardBundle:Message:type.html.twig',array('user'=>$user,'messages'=>$pagination,'type'=>$type));
    }
    
    public function seeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        return $this->render('CoreDashboardBundle:Message:see.html.twig',array('user'=>$user,'message'=>$message));

    }
}
