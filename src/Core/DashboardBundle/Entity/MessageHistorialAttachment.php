<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * MessageHistorialAttachment
 *
 * @ORM\Table(name="MessageHistorialAttachment")
 * @ORM\Entity
 */
class MessageHistorialAttachment
{
     /**
     * @var integer
     *
     * @ORM\Column(name="messageHistorialAttachmentID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
     /**
     * @var \Core\DashboardBundle\Entity\MessageHistorial
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\MessageHistorial", inversedBy="attachments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageHistorialID", referencedColumnName="messageHistorialID")
     * })
     */
    private $messageHistorialID;
    
     /**
     * @var string
     *
     * @ORM\Column(name="attachType", type="string", length=100, nullable=true)
     */
    private $attachType;
    
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=250, nullable=true)
     */
    private $path;
    
    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=50, nullable=true)
     */
    private $size;
    
     /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=50, nullable=true)
     */
    private $extension;
    
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    function getMessageHistorialID() {
        return $this->messageHistorialID;
    }

    function getAttachType() {
        return $this->attachType;
    }

    function getPath() {
        return $this->path;
    }

    function getSize() {
        return $this->size;
    }

    function getExtension() {
        return $this->extension;
    }

    function setMessageHistorialID(\Core\DashboardBundle\Entity\MessageHistorial $messageHistorialID) {
        $this->messageHistorialID = $messageHistorialID;
    }

    function setAttachType($attachType) {
        $this->attachType = $attachType;
    }

    function setPath($path) {
        $this->path = $path;
    }

    function setSize($size) {
        $this->size = $size;
    }

    function setExtension($extension) {
        $this->extension = $extension;
    }


}