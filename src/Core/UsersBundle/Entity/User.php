<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// src/Core/UsersBundle/Entity/User.php

namespace Core\UsersBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
     /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;
    
     /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="createdBy", referencedColumnName="id")
     * })
     */
    private $createdBy;
    
    /**
     * @var \Core\UsersBundle\Entity\Group
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\Group")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dependecyID", referencedColumnName="id")
     * })
     */
    private $dependecyID;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdOn", type="datetime", nullable=true)
     */
    private $createdOn;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="isBoss", type="boolean", nullable=true)
     */
    private $isBoss;
    
     /**
     * @var string
     *
     * @ORM\Column(name="adress", type="string", length=255, nullable=true)
     */
    private $adress;

    public function __construct()
    {
        parent::__construct();
        $this->createdOn = new \DateTime("now");
        $this->messages = new ArrayCollection();
    }
    
    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }
    
    public function __toString() {
        return $this->lastName ." ".$this->firstName;
    }
    
    function getFirstName() {
        return $this->firstName;
    }

    function getLastName() {
        return $this->lastName;
    }

    function getCreatedBy() {
        return $this->createdBy;
    }

    function getCreatedOn() {
        return $this->createdOn;
    }

    function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    function setCreatedBy(\Core\UsersBundle\Entity\User $createdBy) {
        $this->createdBy = $createdBy;
    }

    function setCreatedOn(\DateTime $createdOn) {
        $this->createdOn = $createdOn;
    }
    
    function getIsBoss() {
        return $this->isBoss;
    }

    function setIsBoss($isBoss) {
        $this->isBoss = $isBoss;
    }

        
    /**
     * @ORM\OneToMany(targetEntity="\Core\DashboardBundle\Entity\Message", mappedBy="createdBy" , cascade={"remove"})
     */
    protected $messages;
    
    function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="\Core\DashboardBundle\Entity\NotificationUsers", mappedBy="userID" , cascade={"remove"})
     */
    protected $notifications;
    
    function getNotifications()
    {
        return $this->notifications;
    }
    
    public function setEmail($email)
    {
      parent::setEmail($email);
      $this->setUsername($email);
    }
    
    
    function getAdress() {
        return $this->adress;
    }

    function setAdress($adress) {
        $this->adress = $adress;
    }

    function getDependecyID() {
        return $this->dependecyID;
    }

    function setDependecyID(\Core\UsersBundle\Entity\Group $dependecyID) {
        $this->dependecyID = $dependecyID;
    }

}