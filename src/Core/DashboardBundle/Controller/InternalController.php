<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Core\DashboardBundle\Form\Type\InternalRegisterFormType;
use Core\UsersBundle\Entity\User;

class InternalController extends Controller
{
    public function bossesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $groups = $em->getRepository("CoreUsersBundle:Group")->findAll(); 
        return $this->render('CoreDashboardBundle:Internals:boss.html.twig',array('user'=>$user,'groups'=>$groups));
    
        
    }
    
    public function setBossAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;
        
        if(!empty($data))
        {
            foreach ($data as $key=>$value)
            {
                $query1 = $em->createQuery("UPDATE CoreUsersBundle:User u
                                            SET u.isBoss = 0
                                            WHERE u.dependecyID = :dependecyID")
                            ->setParameter('dependecyID',$key)
                            ->getResult();
                
                $query = $em->createQuery("UPDATE CoreUsersBundle:User u
                                           SET u.isBoss = 1
                                           WHERE u.dependecyID = :dependecyID
                                           AND u.id =:id")
                            ->setParameters(array('dependecyID'=>$key,'id'=>$value))
                            ->getResult();
            }
            $this->get('session')->getFlashBag()->add('setBoss', 'Dependence bosses configured successfully.');
            $url = $this->generateUrl('internals');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notFoundBoss', 'There is no users in dependence please add first.');
            $url = $this->generateUrl('dependence_boss');
        }
        
        $response = new RedirectResponse($url);
        return $response;
    }
    
    public function checkBossAction($role)
    {
        $em = $this->getDoctrine()->getManager();
        $isBoss = $em->createQuery("SELECT u FROM CoreUsersBundle:User u
                                    WHERE u.dependecyID =:role
                                    AND u.isBoss =1")
                     ->setParameter('role',$role)
                     ->getResult();
             
        if(empty($isBoss))
        {
            $status = 'ok';
            $message = "";
        }
        else 
        {
            $status = 'ko';
            $name = $isBoss[0]->getFirstName(). " ".$isBoss[0]->getLastName();
            $message =$name." is the boss in this dependence do you want to change it ?";
        }
        $response = new Response();
        $response->setContent(json_encode(array('status'=>$status,'message'=>$message)));
        return $response;
    }

    public function newAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $internal = new User();
        $form = $this->createForm(new InternalRegisterFormType(), $internal);
        return $this->render('CoreDashboardBundle:Internals:new.html.twig',array('user'=>$user,'form'=>$form->createView()));
    }
    public function CheckUserExistence($email)
    {
        $em = $this->getDoctrine()->getManager();
        $exist = $em->createQuery("SELECT u FROM CoreUsersBundle:User u"
                                ." WHERE u.email=:email")->setParameter("email",$email)->getResult();
        if($exist)
        {
            return TRUE;
        }
        return FALSE;
    }
    public function createAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $internal = new User();
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($internal);
        
        
        $form = $this->createForm(new InternalRegisterFormType(), $internal);
        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);
            if($form->isValid())
            {
                $email = $form->getData()->getEmail();
                $exist = $this->CheckUserExistence($email);
                if($exist)
                {
                    $this->get('session')->getFlashBag()->add('addInternalError', 'Email exists !!!');
                    $url = $this->generateUrl('new_internal');
                }
                else
                {
                    $isBoss = $request->request->get('isBoss');
                    if($isBoss ==='1')
                    {
                        $role = $form->getData()->getDependecyID();
                        $query = $em->createQuery("UPDATE CoreUsersBundle:User u
                                                   SET u.isBoss = 0
                                                   WHERE u.dependecyID = :role")
                            ->setParameter('role',$role)
                            ->getResult();
                        $internal->setIsBoss(TRUE);
                    }
                    else
                    {
                        $internal->setIsBoss(FALSE);
                    }
                    
                    $internal = $form->getData();
                    $password = $encoder->encodePassword($internal->getPassword(), $internal->getSalt());
                    $internal->setPassword($password);
                    $internal->setCreatedBy($user);
                    $internal->setEnabled(TRUE);
                    $internal->setUsername($email);
                    $internal->addRole('ROLE_INTERNAL');
                    
                    
                    $em->persist($internal);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('addInternal', 'Internal created successfully.');
                    $url = $this->generateUrl('internals');
                }

            }
            
        }
      $response = new RedirectResponse($url);
      return $response;
    }
    
    public function listAction(Request $request)
    {
        $em  = $this->get('doctrine.orm.entity_manager');
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $admin = 'ROLE_ADMIN';
        $external = 'ROLE_EXTERNAL';
        
        $dql   = "SELECT u FROM CoreUsersBundle:User u
                  WHERE u.roles NOT LIKE :admin
                  AND u.roles NOT LIKE :external";
        $query = $em->createQuery($dql)->setParameters(array('admin'=>'%"' . $admin . '"%','external'=>'%"' . $external . '"%'));
        
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1),10);
        
        return $this->render('CoreDashboardBundle:Internals:list.html.twig',array('user'=>$user,'internals'=>$pagination));
       
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $internal = $em->getRepository("CoreUsersBundle:User")->find($id);
        $groups = $em->getRepository("CoreUsersBundle:Group")->findAll();
        
        return $this->render('CoreDashboardBundle:Internals:edit.html.twig',array('user'=>$user,
                                                                                  'internal'=>$internal,
                                                                                   'groups'=>$groups
                                                                                 ));
        
    }
    
    public function updateAction($id,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $data = $request->request;
        
        $firstname = $data->get('firstname');
        $lastname = $data->get('lastname');
        $enabled = $data->get('enabled');
        $role = $data->get('role');
        
        $internal = $em->getRepository("CoreUsersBundle:User")->find($id);
        
        $internal->setFirstName($firstname);
        $internal->setLastName($lastname);
        $internal->setEnabled($enabled);
        $dependecy = $em->getRepository("CoreUsersBundle:Group")->find($role);
        $internal->setDependecyID($dependecy);
        
        $em->persist($internal);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('editInternal', 'Internal edited successfully.');
        $url = $this->generateUrl('internals');
        $response = new RedirectResponse($url);
        return $response;
    }
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $internal = $em->getRepository("CoreUsersBundle:User")->find($id);
        
        $em->remove($internal);
        $em->flush();
         
        $this->get('session')->getFlashBag()->add('deleteInternal', 'Internal deleted successfully.');
        $url = $this->generateUrl('internals');
        $response = new RedirectResponse($url);
        return $response;
    }
}
