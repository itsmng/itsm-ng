<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_networkaliases")]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["name"])]
#[ORM\Index(columns: ["networknames_id"])]
class Networkalias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $networknames_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $fqdns_id;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getNetworknamesId(): ?int
    {
        return $this->networknames_id;
    }

    public function setNetworknamesId(int $networknames_id): self
    {
        $this->networknames_id = $networknames_id;

        return $this;
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

    public function getFqdnsId(): ?int
    {
        return $this->fqdns_id;
    }

    public function setFqdnsId(int $fqdns_id): self
    {
        $this->fqdns_id = $fqdns_id;

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
}
