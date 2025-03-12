<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevelcriterias')]
#[ORM\Index(name: 'olalevels_id', columns: ['olalevels_id'])]
#[ORM\Index(name: 'condition', columns: ['condition'])]
class OlaLevelCriteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: OlaLevel::class)]
    #[ORM\JoinColumn(name: 'olalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?OlaLevel $olalevel = null;

    #[ORM\Column(name: 'criteria', type: 'string', length: 255, nullable: true)]
    private $criteria;

    #[ORM\Column(name: 'condition', type: 'integer', options: ['default' => 0, 'comment' => 'see define.php PATTERN_* and REGEX_* constant'])]
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

    public function setCriteria(?string $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCondition(): ?int
    {
        return $this->condition;
    }

    public function setCondition(?int $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }


    /**
     * Get the value of olalevel
     */
    public function getOlaLevel()
    {
        return $this->olalevel;
    }

    /**
     * Set the value of olalevel
     *
     * @return  self
     */
    public function setOlaLevel($olalevel)
    {
        $this->olalevel = $olalevel;

        return $this;
    }
}
