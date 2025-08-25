<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevelcriterias')]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
#[ORM\Index(name: "conditions", columns: ["conditions"])]
class SlaLevelCriteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: SlaLevel::class)]
    #[ORM\JoinColumn(name: 'slalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?SlaLevel $slalevel = null;

    #[ORM\Column(name: 'criteria', type: 'string', length: 255, nullable: true)]
    private $criteria;

    #[ORM\Column(name: 'conditions', type: 'integer', options: ['default' => 0, 'comment' => 'see define.php PATTERN_* and REGEX_* constant'])]
    private $condition;

    #[ORM\Column(name: 'pattern', type: 'string', length: 255, nullable: true)]
    private $pattern;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function setCriteria(string $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCondition(): ?int
    {
        return $this->condition;
    }

    public function setCondition(int $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }


    /**
     * Get the value of slalevel
     */
    public function getSlaLevel()
    {
        return $this->slalevel;
    }

    /**
     * Set the value of slalevel
     *
     * @return  self
     */
    public function setSlaLevel($slalevel)
    {
        $this->slalevel = $slalevel;

        return $this;
    }
}
