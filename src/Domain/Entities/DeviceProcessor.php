<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_deviceprocessors")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "deviceprocessormodels_id", columns: ["deviceprocessormodels_id"])]
class DeviceProcessor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'designation', type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(name: 'frequence', type: "integer", options: ["default" => 0])]
    private $frequence = 0;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'frequency_default', type: "integer", options: ["default" => 0])]
    private $frequencyDefault = 0;

    #[ORM\Column(name: 'nbcores_default', type: "integer", nullable: true)]
    private $nbcoresDefault;

    #[ORM\Column(name: 'nbthreads_default', type: "integer", nullable: true)]
    private $nbthreadsDefault;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive = false;

    #[ORM\ManyToOne(targetEntity: DeviceProcessorModel::class)]
    #[ORM\JoinColumn(name: 'deviceprocessormodels_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceProcessorModel $deviceprocessormodel = null;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getFrequence(): ?int
    {
        return $this->frequence;
    }

    public function setFrequence(?int $frequence): self
    {
        $this->frequence = $frequence;

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

    public function getFrequencyDefault(): ?int
    {
        return $this->frequencyDefault;
    }

    public function setFrequencyDefault(?int $frequencyDefault): self
    {
        $this->frequencyDefault = $frequencyDefault;

        return $this;
    }

    public function getNbcoresDefault(): ?int
    {
        return $this->nbcoresDefault;
    }

    public function setNbcoresDefault(?int $nbcoresDefault): self
    {
        $this->nbcoresDefault = $nbcoresDefault;

        return $this;
    }

    public function getNbthreadsDefault(): ?int
    {
        return $this->nbthreadsDefault;
    }

    public function setNbthreadsDefault(?int $nbthreadsDefault): self
    {
        $this->nbthreadsDefault = $nbthreadsDefault;

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

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
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
     * Get the value of deviceprocessormodel
     */
    public function getDeviceprocessormodel()
    {
        return $this->deviceprocessormodel;
    }

    /**
     * Set the value of deviceprocessormodel
     *
     * @return  self
     */
    public function setDeviceprocessormodel($deviceprocessormodel)
    {
        $this->deviceprocessormodel = $deviceprocessormodel;

        return $this;
    }
}
