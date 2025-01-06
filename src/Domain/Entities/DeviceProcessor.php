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
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $frequence;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $frequency_default;

    #[ORM\Column(type: "integer", nullable: true)]
    private $nbcores_default;

    #[ORM\Column(type: "integer", nullable: true)]
    private $nbthreads_default;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Deviceprocessormodel::class)]
    #[ORM\JoinColumn(name: 'deviceprocessormodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Deviceprocessormodel $deviceprocessormodel;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

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
        return $this->frequency_default;
    }

    public function setFrequencyDefault(?int $frequency_default): self
    {
        $this->frequency_default = $frequency_default;

        return $this;
    }

    public function getNbcoresDefault(): ?int
    {
        return $this->nbcores_default;
    }

    public function setNbcoresDefault(?int $nbcores_default): self
    {
        $this->nbcores_default = $nbcores_default;

        return $this;
    }

    public function getNbthreadsDefault(): ?int
    {
        return $this->nbthreads_default;
    }

    public function setNbthreadsDefault(?int $nbthreads_default): self
    {
        $this->nbthreads_default = $nbthreads_default;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
