<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\UsersBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Dependence")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
     protected $id;
     
     public function __construct()
    {
        $name = $this->name;
        parent::__construct($name, $roles = array());
        $this->users = new ArrayCollection();
    }
    
     /**
     * @ORM\OneToMany(targetEntity="\Core\UsersBundle\Entity\User", mappedBy="dependecyID" , cascade={"remove"})
     */
    protected $users;
    
    function getUsers()
    {
        return $this->users;
    }
     
}