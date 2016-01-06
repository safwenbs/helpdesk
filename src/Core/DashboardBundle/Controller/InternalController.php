<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Core\DashboardBundle\Form\Type\InternalRegisterFormType;
use Core\UsersBundle\Entity\User;

class InternalController extends Controller
{
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
                    $internal = $form->getData();
                    $password = $encoder->encodePassword($internal->getPassword(), $internal->getSalt());
                    $internal->setPassword($password);
                    $internal->setCreatedBy($user);
                    $internal->setEnabled(TRUE);
                    $internal->setUsername($email);
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
        
        return $this->render('CoreDashboardBundle:Internals:edit.html.twig',array('user'=>$user,'internal'=>$internal));
        
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
        if ($internal->isGranted('ROLE_INTERNAL_ACCOUNTING')) {
            $oldRole = 'ROLE_INTERNAL_ACCOUNTING';
        } elseif ($internal->isGranted('ROLE_INTERNAL_ADMINISTRATION')) {
            $oldRole = 'ROLE_INTERNAL_ADMINISTRATION';
        } elseif ($internal->isGranted('ROLE_INTERNAL_RECEPTION')) {
            $oldRole = 'ROLE_INTERNAL_RECEPTION';
        } else {
            $oldRole = 'ROLE_INTERNAL_IT';
        }
        
        $internal->removeRole($oldRole);
        $internal->addRole($role);
        
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
