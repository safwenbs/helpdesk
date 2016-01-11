<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * MessageAttachment
 *
 * @ORM\Table(name="MessageAttachment")
 * @ORM\Entity
 */
class MessageAttachment
{
     /**
     * @var integer
     *
     * @ORM\Column(name="messageAttachmentID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
     /**
     * @var \Core\DashboardBundle\Entity\Message
     *
     * @ORM\ManyToOne(targetEntity="\Core\DashboardBundle\Entity\Message", inversedBy="attachments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageID", referencedColumnName="messageID")
     * })
     */
    private $messageID;
    
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
    function getMessageID() {
        return $this->messageID;
    }

    function setMessageID(\Core\DashboardBundle\Entity\Message $messageID) {
        $this->messageID = $messageID;
    }
    
    function size2Byte($size) 
    {
        $units = array('KB', 'MB', 'GB', 'TB');
        $currUnit = '';
        while (count($units) > 0 && $size > 1024) {
            $currUnit = array_shift($units);
            $size /= 1024;
        }
        return ($size | 0) . $currUnit;
    }
}