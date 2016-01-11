<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MessageHistorial
 *
 * @ORM\Table(name="MessageHistorial")
 * @ORM\Entity
 */
class MessageHistorial
{
     /**
     * @var integer
     *
     * @ORM\Column(name="messageHistorialID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
     /**
     * @var \Core\DashboardBundle\Entity\Message
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\Message", inversedBy="historics")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageID", referencedColumnName="messageID")
     * })
     */
    private $messageID;
    
     /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdOn", type="datetime", nullable=true)
     */
    private $createdOn;
    
     /**
     * @var string
     *
     * @ORM\Column(name="serviceFrom", type="string", length=50, nullable=true)
     */
    private $serviceFrom;
    
    /**
     * @var string
     *
     * @ORM\Column(name="serviceTo", type="string", length=50, nullable=true)
     */
    private $serviceTo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=true)
     */
    private $messageStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=50, nullable=true)
     */
    private $action;
    
    /**
     * @var string
     *
     * @ORM\Column(name="textResponse", type="text", nullable=true)
     */
    private $textResponse;
    
    /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userSender", referencedColumnName="id")
     * })
     */
    private $userSender;
    
    /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userReceiver", referencedColumnName="id")
     * })
     */
    private $userReceiver;
    
    
    
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
        $this->attachments = new ArrayCollection();
    }
    
    function getMessageID() {
        return $this->messageID;
    }

    function getCreatedOn() {
        return $this->createdOn;
    }

    function getServiceFrom() {
        return $this->serviceFrom;
    }

    function getServiceTo() {
        return $this->serviceTo;
    }

    function getMessageStatus() {
        return $this->messageStatus;
    }

    function getTextResponse() {
        return $this->textResponse;
    }

    function getUserSender() {
        return $this->userSender;
    }

    function getUserReceiver() {
        return $this->userReceiver;
    }

    function setMessageID(\Core\DashboardBundle\Entity\Message $messageID) {
        $this->messageID = $messageID;
    }

    function setCreatedOn(\DateTime $createdOn) {
        $this->createdOn = $createdOn;
    }

    function setServiceFrom($serviceFrom) {
        $this->serviceFrom = $serviceFrom;
    }

    function setServiceTo($serviceTo) {
        $this->serviceTo = $serviceTo;
    }

    function setMessageStatus($messageStatus) {
        $this->messageStatus = $messageStatus;
    }

    function setTextResponse($textResponse) {
        $this->textResponse = $textResponse;
    }

    function setUserSender(\Core\UsersBundle\Entity\User $userSender) {
        $this->userSender = $userSender;
    }

    function setUserReceiver(\Core\UsersBundle\Entity\User $userReceiver) {
        $this->userReceiver = $userReceiver;
    }
    
     /**
     * @ORM\OneToMany(targetEntity="\Core\DashboardBundle\Entity\MessageHistorialAttachment", mappedBy="messageHistorialID" , cascade={"remove"})
     */
    protected $attachments;
    
    function getAttachments()
    {
        return $this->attachments;
    }

    function getAction() {
        return $this->action;
    }

    function setAction($action) {
        $this->action = $action;
    }


}