<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicegraphiccards')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'interfacetypes_id', columns: ['interfacetypes_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'chipset', columns: ['chipset'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'devicegraphiccardmodels_id', columns: ['devicegraphiccardmodels_id'])]
class Devicegraphiccard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: 'integer', name: 'interfacetypes_id', options: ['default' => 0])]
    private $interfacetypes_id;

    #[ORM\ManyToOne(targetEntity: InterfaceType::class)]
    #[ORM\JoinColumn(name: 'interfacetypes_id', referencedColumnName: 'id', nullable: false)]
    private ?InterfaceType $interfacetype;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', name: 'manufacturers_id', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $memory_default;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $devicegraphiccardmodels_id;

    #[ORM\ManyToOne(targetEntity: DeviceGraphiccardModel::class)]
    #[ORM\JoinColumn(name: 'devicegraphiccardmodels_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceGraphiccardModel $devicegraphiccardmodel;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $chipset;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

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

    public function getInterfacetypesId(): ?int
    {
        return $this->interfacetypes_id;
    }

    public function setInterfacetypesId(?int $interfacetypes_id): self
    {
        $this->interfacetypes_id = $interfacetypes_id;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getMemoryDefault(): ?int
    {
        return $this->memory_default;
    }

    public function setMemoryDefault(?int $memory_default): self
    {
        $this->memory_default = $memory_default;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

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

    public function getDevicegraphiccardmodelsId(): ?int
    {
        return $this->devicegraphiccardmodels_id;
    }

    public function setDevicegraphiccardmodelsId(?int $devicegraphiccardmodels_id): self
    {
        $this->devicegraphiccardmodels_id = $devicegraphiccardmodels_id;

        return $this;
    }

    public function getChipset(): ?string
    {
        return $this->chipset;
    }

    public function setChipset(?string $chipset): self
    {
        $this->chipset = $chipset;

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
     * Get the value of interfacetype
     */
    public function getInterfacetype()
    {
        return $this->interfacetype;
    }

    /**
     * Set the value of interfacetype
     *
     * @return  self
     */
    public function setInterfacetype($interfacetype)
    {
        $this->interfacetype = $interfacetype;

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
     * Get the value of devicegraphiccardmodel
     */
    public function getDevicegraphiccardmodel()
    {
        return $this->devicegraphiccardmodel;
    }

    /**
     * Set the value of devicegraphiccardmodel
     *
     * @return  self
     */
    public function setDevicegraphiccardmodel($devicegraphiccardmodel)
    {
        $this->devicegraphiccardmodel = $devicegraphiccardmodel;

        return $this;
    }
}
