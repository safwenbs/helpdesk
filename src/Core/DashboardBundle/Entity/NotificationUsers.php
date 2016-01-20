<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationUsers
 *
 * @ORM\Table(name="NotificationUsers")
 * @ORM\Entity
 */
class NotificationUsers
{
     /**
     * @var integer
     *
     * @ORM\Column(name="notificationUserID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
     /**
     * @var \Core\DashboardBundle\Entity\Notification
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\Notification")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notificationID", referencedColumnName="notificationID")
     * })
     */
    private $notificationID;
    
    /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userID", referencedColumnName="id")
     * })
     */
    private $userID;
      
    /**
     * @var boolean
     *
     * @ORM\Column(name="isViewed", type="boolean", nullable=true)
     */
    private $isViewed;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct()
    {
        $this->isViewed = FALSE;
    }
    
    function getNotificationID() {
        return $this->notificationID;
    }

    function getUserID() {
        return $this->userID;
    }

    function getIsViewed() {
        return $this->isViewed;
    }

    function setNotificationID(\Core\DashboardBundle\Entity\Notification $notificationID) {
        $this->notificationID = $notificationID;
    }

    function setUserID(\Core\UsersBundle\Entity\User $userID) {
        $this->userID = $userID;
    }

    function setIsViewed($isViewed) {
        $this->isViewed = $isViewed;
    }


}





