<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="Notification")
 * @ORM\Entity
 */
class Notification
{
     /**
     * @var integer
     *
     * @ORM\Column(name="notificationID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
     /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdOn", type="datetime", nullable=true)
     */
    private $createdOn;
    
     /**
     * @var \Core\DashboardBundle\Entity\Message
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\Message")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageID", referencedColumnName="messageID")
     * })
     */
    private $messageID;
     
    /**
     * @var string
     *
     * @ORM\Column(name="notif", type="text", nullable=true)
     */
    private $notif;
     
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
        $this->createdOn = new \DateTime("now");
    }
    function getCreatedOn() {
        return $this->createdOn;
    }

    function getMessageID() {
        return $this->messageID;
    }

    function getNotif() {
        return $this->notif;
    }

    function setCreatedOn(\DateTime $createdOn) {
        $this->createdOn = $createdOn;
    }

    function setMessageID(\Core\DashboardBundle\Entity\Message $messageID) {
        $this->messageID = $messageID;
    }

    function setNotif($notif) {
        $this->notif = $notif;
    }
     
    
}





