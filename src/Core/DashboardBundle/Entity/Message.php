<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table(name="Message")
 * @ORM\Entity
 */
class Message
{
     /**
     * @var integer
     *
     * @ORM\Column(name="messageID", type="integer", nullable=false)
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
     * @var \DateTime
     *
     * @ORM\Column(name="closedOn", type="datetime", nullable=true)
     */
    private $closedOn;
    
    
    /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="createdBy", referencedColumnName="id")
     * })
     */
    private $createdBy;
    
    /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="closedBy", referencedColumnName="id")
     * })
     */
    private $closedBy;
    
    /**
     * @var string
     *
     * @ORM\Column(name="currentService", type="string", length=30, nullable=true)
     */
    private $currentService;
    
    /**
     * @var string
     *
     * @ORM\Column(name="responseText", type="string", length=30, nullable=true)
     */
    private $responseText;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=30, nullable=true)
     */
    private $status;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=30, nullable=true)
     */
    private $code;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="isViewed", type="boolean", nullable=true)
     */
    private $isViewed;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="isTreated", type="boolean", nullable=true)
     */
    private $isTreated;
    
    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;
    
     /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;
    
    /**
     * @var \Core\DashboardBundle\Entity\Demand
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\Demand")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demandType", referencedColumnName="demandID")
     * })
     */
    private $demandType;
    
     /**
     * @var \Core\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Core\UsersBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="viewedBy", referencedColumnName="id")
     * })
     */
    private $viewedBy;
    
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="contactType", type="string", length=25, nullable=true)
     */
    private $contactType;
    
    

    
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
        $this->currentService ="Reception";
        $this->isTreated = FALSE;
        $this->isViewed = FALSE;
        $this->status = "opened";
    }
    
    function getCreatedOn() {
        return $this->createdOn;
    }

    function getCreatedBy() {
        return $this->createdBy;
    }

    function getCurrentService() {
        return $this->currentService;
    }

    function getIsViewed() {
        return $this->isViewed;
    }

    function getIsTreated() {
        return $this->isTreated;
    }

    function getSubject() {
        return $this->subject;
    }

    function getMessage() {
        return $this->message;
    }

    function getDemandType() {
        return $this->demandType;
    }

    function getViewedBy() {
        return $this->viewedBy;
    }

    

    function getContactType() {
        return $this->contactType;
    }

    function setCreatedOn(\DateTime $createdOn) {
        $this->createdOn = $createdOn;
    }

    function setCreatedBy(\Core\UsersBundle\Entity\User $createdBy) {
        $this->createdBy = $createdBy;
    }

    function setCurrentService($currentService) {
        $this->currentService = $currentService;
    }

    function setIsViewed($isViewed) {
        $this->isViewed = $isViewed;
    }

    function setIsTreated($isTreated) {
        $this->isTreated = $isTreated;
    }

    function setSubject($subject) {
        $this->subject = $subject;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function setDemandType(\Core\DashboardBundle\Entity\Demand $demandType) {
        $this->demandType = $demandType;
    }

    function setViewedBy(\Core\UsersBundle\Entity\User $viewedBy) {
        $this->viewedBy = $viewedBy;
    }

    

    function setContactType($contactType) {
        $this->contactType = $contactType;
    }
    function getStatus() {
        return $this->status;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getExpireOn()
    {
      $creationDate = date_format($this->createdOn,('Y-m-d'));
      $skipdays = array("Saturday", "Sunday");
      $skipdates = array();
      $endDate = $this->addDays(strtotime($creationDate), $this->demandType->getNbDaysTreatement(),$skipdays,$skipdates);
      return $endDate;
    }
    
    function addDays($timestamp, $days, $skipdays = array("Saturday", "Sunday"), $skipdates = NULL)
    {
        $i = 1;

        while ($days >= $i) {
            $timestamp = strtotime("+1 day", $timestamp);
            if ( (in_array(date("l", $timestamp), $skipdays)) || (in_array(date("Y-m-d", $timestamp), $skipdates)) )
            {
                $days++;
            }
            $i++;
        }
        return date("d-m-Y",$timestamp);
    }
    
    function getCode() {
        return $this->code;
    }

    function setCode($code) {
        $this->code = $code;
    }
    
    function getClosedOn() {
        return $this->closedOn;
    }

    function getClosedBy() {
        return $this->closedBy;
    }

    function getResponseText() {
        return $this->responseText;
    }

    function setClosedOn(\DateTime $closedOn) {
        $this->closedOn = $closedOn;
    }

    function setClosedBy(\Core\UsersBundle\Entity\User $closedBy) {
        $this->closedBy = $closedBy;
    }

    function setResponseText($responseText) {
        $this->responseText = $responseText;
    }



}