<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_networkaliases")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "networknames_id", columns: ["networknames_id"])]
class Networkalias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne(targetEntity: Networkname::class)]
    #[ORM\JoinColumn(name: 'networknames_id', referencedColumnName: 'id', nullable: true)]
    private ?Networkname $networkname = null;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Fqdn::class)]
    #[ORM\JoinColumn(name: 'fqdns_id', referencedColumnName: 'id', nullable: true)]
    private ?Fqdn $fqdn = null;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    /**
     * Get the value of networkname
     */
    public function getNetworkname()
    {
        return $this->networkname;
    }

    /**
     * Set the value of networkname
     *
     * @return  self
     */
    public function setNetworkname($networkname)
    {
        $this->networkname = $networkname;

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
     * Get the value of fqdn
     */
    public function getFqdn()
    {
        return $this->fqdn;
    }

    /**
     * Set the value of fqdn
     *
     * @return  self
     */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;

        return $this;
    }
}
