<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changetemplatemandatoryfields')]
#[ORM\UniqueConstraint(name: 'changetemplates_id_num', columns: ['changetemplates_id', 'num'])]
#[ORM\Index(name: 'changetemplates_id', columns: ['changetemplates_id'])]

class Changetemplatemandatoryfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'changetemplates_id', options: ['default' => 0])]
    private $changetemplates_id;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?ChangeTemplate $changetemplate;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChangetemplatesId(): ?int
    {
        return $this->changetemplates_id;
    }

    public function setChangetemplatesId(int $changetemplates_id): self
    {
        $this->changetemplates_id = $changetemplates_id;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): self
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get the value of changetemplate
     */
    public function getChangetemplate()
    {
        return $this->changetemplate;
    }

    /**
     * Set the value of changetemplate
     *
     * @return  self
     */
    public function setChangetemplate($changetemplate)
    {
        $this->changetemplate = $changetemplate;

        return $this;
    }
}
