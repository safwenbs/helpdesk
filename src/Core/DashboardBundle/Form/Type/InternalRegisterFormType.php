<?php

namespace Core\DashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class InternalRegisterFormType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname')
                ->add('lastname')
                ->add('email')
                ->add('password','password')
                ->add('dependecyID', 'entity', array(
                    'class' => 'Core\UsersBundle\Entity\Group','property' => 'name',
                    'label' => false,
                    'attr'    => array('class' => 'form-control'),
                    )
                     )
                ->remove('roles')
//                ->add('roles', 'collection', array(
//                   'type' => 'choice',
//                   'options' => array(
//                       'label' => false,
//                       'attr'    => array('class' => 'form-control'),
//                       'required'  => true,
//                       'choices' => array(
//                           'ROLE_INTERNAL_RECEPTION' => 'Reception',
//                           'ROLE_INTERNAL_ACCOUNTING'  => 'Accounting',
//                           'ROLE_INTERNAL_ADMINISTRATION' => 'Administration',
//                           'ROLE_INTERNAL_IT'  => 'IT'
//                       )
//                   )
//               )
//           )
                
                
                ;
    }

    public function getName()
    {
        return 'helpdesk_add_internal';
    }
    
}