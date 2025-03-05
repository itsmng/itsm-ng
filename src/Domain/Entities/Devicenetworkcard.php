<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicenetworkcards')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'devicenetworkcardmodels_id', columns: ['devicenetworkcardmodels_id'])]
class Devicenetworkcard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'designation', type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(name: 'bandwidth', type: 'string', length: 255, nullable: true)]
    private $bandwidth;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'mac_default', type: 'string', length: 255, nullable: true)]
    private $macDefault;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;


    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: Devicenetworkcardmodel::class)]
    #[ORM\JoinColumn(name: 'devicenetworkcardmodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Devicenetworkcardmodel $devicenetworkcardmodel = null;


    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getBandwidth(): ?string
    {
        return $this->bandwidth;
    }

    public function setBandwidth(?string $bandwidth): self
    {
        $this->bandwidth = $bandwidth;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getMacDefault(): ?string
    {
        return $this->macDefault;
    }

    public function setMacDefault(?string $macDefault): self
    {
        $this->macDefault = $macDefault;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }


    /**
     * Get the value of manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set the value of manufacturer
     *
     * @return  self
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of devicenetworkcardmodel
     */
    public function getDevicenetworkcardmodel()
    {
        return $this->devicenetworkcardmodel;
    }

    /**
     * Set the value of devicenetworkcardmodel
     *
     * @return  self
     */
    public function setDevicenetworkcardmodel($devicenetworkcardmodel)
    {
        $this->devicenetworkcardmodel = $devicenetworkcardmodel;

        return $this;
    }
}
