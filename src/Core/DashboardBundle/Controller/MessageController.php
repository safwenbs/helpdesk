<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Core\DashboardBundle\Entity\Message;
use Core\DashboardBundle\Entity\MessageAttachment;
use Core\DashboardBundle\Entity\MessageHistorial;
use Core\DashboardBundle\Entity\MessageHistorialAttachment;

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
        $adress = $data->get('adress');
        if(!empty($adress))
        {
          $user->setAdress($adress);
          $em->persist($user);
        }
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
        
        
        $files = $request->files->get('attachments');
        foreach ($files as $file)
        {
            if($file !=NULL)
            {
                $extension = $file->getClientOriginalExtension();
                $name = $file->getClientOriginalName();
                $type = $file->getClientMimeType();
                $size = $file->getClientSize();
                $path = "request_".$message->getId()."_".$name;
                $file->move("attachments/",$path);
                
                $messageAttachment = new MessageAttachment();
                $messageAttachment->setAttachType($type);
                $messageAttachment->setExtension($extension);
                $messageAttachment->setMessageID($message);
                $messageAttachment->setPath($path);
                $messageAttachment->setSize($size);
                $em->persist($messageAttachment);
                $em->flush();
            }
           
        }
        
        
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
        $service = $user->getDependecyID()->getName();
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
                                          AND m.canBeViewedBy =:id
                                          ORDER BY m.createdOn DESC")->setParameters(array("service"=>$service,"id"=>$user->getId()));
            
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
        $service =$message->getCurrentService();
        
        
        $services = $em->createQuery("SELECT g.name FROM CoreUsersBundle:Group g
                                      WHERE g.id IN (SELECT identity(u.dependecyID) FROM CoreUsersBundle:User u WHERE u.isBoss =1 AND u.enabled =1 )
                                      AND g.name != :service")
                        ->setParameter("service",$service)
                        ->getResult();
        $message->setIsViewed(TRUE);
        $message->setStatus("ongoing");
        $em->persist($message);
        $em->flush();
        
        $users = $this->getusersService($user->getDependecyID()->getID());
        return $this->render('CoreDashboardBundle:Message:treat.html.twig',array('user'=>$user,'message'=>$message,'services'=>$services,'users'=>$users));
    }
    public function moveAction($id,Request $request)
    {
        $data = $request->request;
        $service = $data->get('toservice');
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        $oldService = $message->getCurrentService();
        $message->setCurrentService($service);
        $message->setCanBeViewed(FALSE);
        $em->persist($message);
        $em->flush();
        
        $textResponse = $data->get('textResponse');
        $messageHistorial = new MessageHistorial();
        $messageHistorial->setMessageID($message);
        $messageHistorial->setMessageStatus($message->getStatus());
        $messageHistorial->setServiceFrom($oldService);
        $messageHistorial->setServiceTo($service);
        $messageHistorial->setTextResponse($textResponse);
        $messageHistorial->setUserSender($user);
        $receiver = $this->getBossDependence($service);
        $messageHistorial->setUserReceiver($receiver);
        $messageHistorial->setAction('move');
        $em->persist($messageHistorial);
        $em->flush();
        
        $files = $request->files->get('attachments');
        foreach ($files as $file)
        {
            if($file !=NULL)
            {
                $extension = $file->getClientOriginalExtension();
                $name = $file->getClientOriginalName();
                $type = $file->getClientMimeType();
                $size = $file->getClientSize();
                $path = "historial_".$messageHistorial->getId()."_request_".$message->getId()."_".$name;
                $file->move("attachments/",$path);
                
                $messageHistorialAttachment = new MessageHistorialAttachment();
                $messageHistorialAttachment->setAttachType($type);
                $messageHistorialAttachment->setExtension($extension);
                $messageHistorialAttachment->setMessageHistorialID($messageHistorial);
                $messageHistorialAttachment->setPath($path);
                $messageHistorialAttachment->setSize($size);
                $em->persist($messageHistorialAttachment);
                $em->flush();
            }
           
        }
        
        $this->get('session')->getFlashBag()->add('moveRequest', 'Request moved to '.$service.' service successfully !! ');
        $url = $this->generateUrl('messages_internals');
        $response = new RedirectResponse($url);
        return $response;
    }
    public function shareAction($id,Request $request)
    {
        $data = $request->request;
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $share = $data->get("share");
        $receiverID = $data->get('receiverID');
        if($receiverID !=NULL)
        {
           $receiver = $em->getRepository("CoreUsersBundle:User")->find($receiverID);    
        }
        
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        $message->setCanBeViewed($share);
        if($receiverID !=NULL)
        {
           $message->setCanBeViewedBy($receiver);     
        }
        else $message->setCanBeViewedBy(NULL);
       
        $em->persist($message);
        $em->flush();
        
        
        $service = $message->getCurrentService();
        $textResponse = $data->get('textResponse');
        $messageHistorial = new MessageHistorial();
        $messageHistorial->setMessageID($message);
        $messageHistorial->setMessageStatus($message->getStatus());
        $messageHistorial->setServiceFrom($service);
        $messageHistorial->setServiceTo($service);
        $messageHistorial->setTextResponse($textResponse);
        $messageHistorial->setUserSender($user);
        if($receiverID !=NULL)
        {
          $messageHistorial->setUserReceiver($receiver);
        }
        if($share)
        {
           $messageHistorial->setAction('share');    
        }
        else
        {
            $messageHistorial->setAction('disable sharing');
        }
        
        $em->persist($messageHistorial);
        $em->flush();
        
        $files = $request->files->get('attachments');
        foreach ($files as $file)
        {
            if($file !=NULL)
            {
                $extension = $file->getClientOriginalExtension();
                $name = $file->getClientOriginalName();
                $type = $file->getClientMimeType();
                $size = $file->getClientSize();
                $path = "historial_".$messageHistorial->getId()."_request_".$message->getId()."_".$name;
                $file->move("attachments/",$path);
                
                $messageHistorialAttachment = new MessageHistorialAttachment();
                $messageHistorialAttachment->setAttachType($type);
                $messageHistorialAttachment->setExtension($extension);
                $messageHistorialAttachment->setMessageHistorialID($messageHistorial);
                $messageHistorialAttachment->setPath($path);
                $messageHistorialAttachment->setSize($size);
                $em->persist($messageHistorialAttachment);
                $em->flush();
            }
           
        }
        
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
        
        $service = $message->getCurrentService();
        $messageHistorial = new MessageHistorial();
        $messageHistorial->setMessageID($message);
        $messageHistorial->setMessageStatus($message->getStatus());
        $messageHistorial->setServiceFrom($service);
        $messageHistorial->setServiceTo($service);
        $messageHistorial->setTextResponse($responseText);
        $messageHistorial->setUserSender($user);
        $messageHistorial->setUserReceiver($user);
        $messageHistorial->setAction('close');    
        $em->persist($messageHistorial);
        $em->flush();
        
        
        //SEND EMAIL TO THE EXTERNAL
        /*
         if($message->getContactType()==='mail')
         { 
                $mail = \Swift_Message::newInstance()
                        ->setSubject("Your request has been treated and closed ")
                        ->setFrom('safwen.bensalem@mapp-net.com')
                        ->setTo($message->getCreatedBy()->getEmail())
                        ->setContentType("text/html")
                        ->setBody('CoreDashboardBundle:Message:mail.html.twig',array('message'=>$message))
                        ;
               $this->get('mailer')->send($mail);
         }
         */
        
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
    
    public function getBossDependence($service)
    {
        $em = $this->getDoctrine()->getManager();
        $groupManager = $this->get('fos_user.group_manager');
        $dependecy = $groupManager->findGroupByName($service);
        $getBoss = $em->createQuery("SELECT u FROM CoreUsersBundle:User u
                                     WHERE u.dependecyID = :dependecyID
                                     AND u.isBoss =1
                                     AND u.enabled = 1")
                     ->setParameter('dependecyID',$dependecy->getID())
                     ->getResult();
        if($getBoss)
        {
            $boss = $getBoss[0];
        }
        else
        {
            $boss = NULL;
        }
        
        return $boss;
    }
    
    public function getusersService($dependecyID)
    {
        $em = $this->getDoctrine()->getManager();
        
        $getUsers = $em->createQuery("SELECT u FROM CoreUsersBundle:User u
                                      WHERE u.dependecyID = :dependecyID
                                      AND u.isBoss =0
                                      AND u.enabled = 1")
                     ->setParameter('dependecyID',$dependecyID)
                     ->getResult();
        
        return $getUsers;
    }
    
    public function historialAction($id,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $message = $em->getRepository("CoreDashboardBundle:Message")->find($id);
        
        $historial = $message->getHistorial();
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($historial, $request->query->getInt('page', 1),10);
   
        return $this->render('CoreDashboardBundle:Message:historial.html.twig',array('user'=>$user,'historials'=>$pagination));

    }
    
    public function notifsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $notifs = $user->getNotifications();
        $items = array();
        foreach ($notifs as $notif)
        {
            if($notif->getIsViewed()== FALSE)
            {
                array_push($items, $notif);
            }
            $notif->setIsViewed(TRUE);
            $em->persist($notif);
            $em->flush();
        }
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($items, $request->query->getInt('page', 1),4);
   
        return $this->render('CoreDashboardBundle:Message:notifs.html.twig',array('user'=>$user,'notifs'=>$pagination));

    }
}
