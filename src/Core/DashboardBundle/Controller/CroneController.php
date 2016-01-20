<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Core\DashboardBundle\Entity\Notification;
use Core\DashboardBundle\Entity\NotificationUsers;

class CroneController extends Controller
{
    public function archiveAction()
    {
        $em = $this->getDoctrine()->getManager();
        $today = new \DateTime('now');
        $status = "closed";
        $messages = $em->createQuery("SELECT message FROM CoreDashboardBundle:Message message
                                      WHERE message.status != :status ")->setParameter("status",$status)->getResult();
        foreach ($messages as $message)
        {
            $expireOn = new \DateTime($message->getExpireOn());
            if($today > $expireOn)
            {
                $message->setResponseText("Archived automatically because treatement time has expired");
                $message->setStatus("archived");
                $em->persist($message);
                $em->flush();
            }
        }
        
        $response = new Response();
        $response->setContent("archive");
        return $response;
    }
    
    public function alertAction()
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->createQuery("SELECT message FROM CoreDashboardBundle:Message message
                                      WHERE message.status NOT IN ('closed','archived') ")->getResult();
        foreach ($messages as $message)
        {
            $expireOn = new \DateTime($message->getExpireOn());
            $today = new \DateTime('now');
            if($today < $expireOn)
            {
                 $days = $today->diff($expireOn);
                 $nb = $days->days;
                 if($nb == 2)
                 {
                     $txt = "Please notice that the request ".$message->getSubject()." that"
                             . " has been assigned to you will expire on 2 days and is still not treated"."\n"
                             . " Please think about treating it or it will be archived automatically on ".$message->getExpireOn();
                     
                     $notification = new Notification();
                     $notification->setMessageID($message);
                     $notification->setNotif($txt);
                     $em->persist($notification);
                     $em->flush();
                     
                     $dependecyName = $message->getCurrentService();
                     $groupManager = $this->get('fos_user.group_manager');
                     $dependecy = $groupManager->findGroupByName($dependecyName);
                     $users =  $em->createQuery("SELECT u FROM CoreUsersBundle:User u
                                                 WHERE u.dependecyID = :dependecyID
                                                 AND u.enabled = 1")
                                   ->setParameter('dependecyID',$dependecy->getID())
                                   ->getResult();
                    foreach ($users as $internal)
                    {
                        $notifUser = new NotificationUsers();
                        $notifUser->setNotificationID($notification);
                        $notifUser->setUserID($internal);
                        $em->persist($notifUser);
                        $em->flush();
                    }
                 }
            }
        }
        
        $response = new Response();
        $response->setContent("alert");
        return $response;
        
    }
}
