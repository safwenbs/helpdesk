<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\DashboardBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Demand
 *
 * @ORM\Table(name="Demand")
 * @ORM\Entity
 */
class Demand
{
     /**
     * @var integer
     *
     * @ORM\Column(name="demandID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="demandType", type="string", length=255, nullable=true)
     */
    private $demandType;
    
    /**
     * @var integer 
     * 
     * @ORM\Column(name="nbDaysTreatement", type="integer", nullable=true)
     */
    private $nbDaysTreatement;
    
    
   
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    
    function getDemandType() {
        return $this->demandType;
    }

    function getNbDaysTreatement() {
        return $this->nbDaysTreatement;
    }

    function setDemandType($demandType) {
        $this->demandType = $demandType;
    }

    function setNbDaysTreatement($nbDaysTreatement) {
        $this->nbDaysTreatement = $nbDaysTreatement;
    }


}