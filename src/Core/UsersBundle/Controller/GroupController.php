<?php

namespace Core\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GroupEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseGroupEvent;
use FOS\UserBundle\Event\FilterGroupResponseEvent;
use FOS\UserBundle\Controller\GroupController as BaseController;


class GroupController extends BaseController
{
    public function listAction()
    {
        $request = $this->get('Request');
        $user = $this->container->get('security.context')->getToken()->getUser();
       
        
        $groups = $this->get('fos_user.group_manager')->findGroups();
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($groups, $request->query->getInt('page', 1),10);
       
        return $this->render('FOSUserBundle:Group:list.html.twig', array(
            'groups' => $pagination,
            'user' =>$user
        ));
    }
    
    public function newAction(Request $request)
    {
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        $groupManager = $this->get('fos_user.group_manager');
        $formFactory = $this->get('fos_user.group.form.factory');
        $dispatcher = $this->get('event_dispatcher');

        $group = $groupManager->createGroup('');

        $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_INITIALIZE, new GroupEvent($group, $request));

        $form = $formFactory->createForm();
        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_SUCCESS, $event);
            $result = $groupManager->findGroupByName($group->getName());
            if(!$result)
            {
                $value = str_replace(" ", "_", $group->getName());
                $role = 'ROLE_INTERNAL_'.strtoupper($value);
                $group->addRole($role);
                $group->addRole('ROLE_INTERNAL');
                $groupManager->updateGroup($group);

                if (null === $response = $event->getResponse()) {
                    $this->get('session')->getFlashBag()->add('addGroup','Dependecy created successfully.');
                    $url = $this->generateUrl('fos_user_group_list');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('errorGroup','Dependecy '.$group->getName().' already exists!!!');
                $url = $this->generateUrl('fos_user_group_new');
                $response = new RedirectResponse($url);
            }
            
            
            return $response;
        }

        return $this->render('FOSUserBundle:Group:new.html.twig', array(
            'form' => $form->createview(),
            'user' => $user
        ));
    }
    
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository("CoreUsersBundle:Group")->find($id);
        
        $em->remove($group);
        $em->flush();
        
        $response = new RedirectResponse($this->generateUrl('fos_user_group_list'));
        $this->get('session')->getFlashBag()->add('deleteGroup','Dependecy '.$group->getName().' deleted successfully.');
               
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(FOSUserEvents::GROUP_DELETE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

        return $response;
    }
    
    public function showAction($id)
    {
        $request = $this->get('Request');
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository("CoreUsersBundle:Group")->find($id);
       
        
        $users = $group->getUsers();
        $paginator  = $this->get('knp_paginator');
        
        $pagination = $paginator->paginate($users, $request->query->getInt('page', 1),10);
       
        return $this->render('FOSUserBundle:Group:show.html.twig', array(
            'group' => $group,
            'user' =>$user,
            'internals' =>$pagination
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository("CoreUsersBundle:Group")->find($id);

        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseGroupEvent($group, $request);
        $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.group.form.factory');

        $form = $formFactory->createForm();
        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
            $groupManager = $this->get('fos_user.group_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_SUCCESS, $event);
            $result = $groupManager->findGroupByName($group->getName());
            if(!$result)
            {
                $groupManager->updateGroup($group);

                if (null === $response = $event->getResponse()) {
                    $this->get('session')->getFlashBag()->add('editGroup','Dependecy '.$group->getName().' edited successfully.');
                    $url = $this->generateUrl('fos_user_group_list');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

                
            }
            else
            {
                $this->get('session')->getFlashBag()->add('errorEditGroup','Dependecy '.$group->getName().' already exists!!!');
                $url = $this->generateUrl('fos_user_group_edit',array('id'=>$id));
                $response = new RedirectResponse($url);
                
            }

           
            return $response;
        }

        return $this->render('FOSUserBundle:Group:edit.html.twig', array(
            'form'      => $form->createview(),
            'group'  => $group,
            'user' => $user
        ));
    }
}
