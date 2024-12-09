<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_entities_knowbaseitems')]
#[ORM\Index(name: 'knowbaseitems_id', columns: ['knowbaseitems_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
class EntityKnowbaseitem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $knowbaseitems_id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class, inversedBy: 'entityKnowbaseitems')]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: false)]
    private ?Knowbaseitem $knowbaseitem;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'entityKnowbaseitems')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKnowbaseitemsId(): ?int
    {
        return $this->knowbaseitems_id;
    }

    public function setKnowbaseitemsId(?int $knowbaseitems_id): self
    {
        $this->knowbaseitems_id = $knowbaseitems_id;

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


    /**
     * Get the value of knowbaseitem
     */
    public function getKnowbaseitem()
    {
        return $this->knowbaseitem;
    }

    /**
     * Set the value of knowbaseitem
     *
     * @return  self
     */
    public function setKnowbaseitem($knowbaseitem)
    {
        $this->knowbaseitem = $knowbaseitem;

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
}
