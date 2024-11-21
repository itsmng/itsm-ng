<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_impactcontexts")]
class ImpactContext
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "text", length: 65535)]
    private $positions;

    #[ORM\Column(type: "float", options: ["default" => 0])]
    private $zoom;

    #[ORM\Column(type: "float", options: ["default" => 0])]
    private $pan_x;

    #[ORM\Column(type: "float", options: ["default" => 0])]
    private $pan_y;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $impact_color;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $depends_color;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $impact_and_depends_color;

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private $show_depends;

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private $show_impact;

    #[ORM\Column(type: "integer", options: ["default" => 5])]
    private $max_depth;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPositions(string $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    public function getPositions(): ?string
    {
        return $this->positions;
    }

    public function setZoom(float $zoom): self
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getZoom(): ?float
    {
        return $this->zoom;
    }

    public function setPanX(float $pan_x): self
    {
        $this->pan_x = $pan_x;

        return $this;
    }

    public function getPanX(): ?float
    {
        return $this->pan_x;
    }

    public function setPanY(float $pan_y): self
    {
        $this->pan_y = $pan_y;

        return $this;
    }

    public function getPanY(): ?float
    {
        return $this->pan_y;
    }

    public function setImpactColor(string $impact_color): self
    {
        $this->impact_color = $impact_color;

        return $this;
    }

    public function getImpactColor(): ?string
    {
        return $this->impact_color;
    }

    public function setDependsColor(string $depends_color): self
    {
        $this->depends_color = $depends_color;

        return $this;
    }

    public function getDependsColor(): ?string
    {
        return $this->depends_color;
    }

    public function setImpactAndDependsColor(string $impact_and_depends_color): self
    {
        $this->impact_and_depends_color = $impact_and_depends_color;

        return $this;
    }

    public function getImpactAndDependsColor(): ?string
    {
        return $this->impact_and_depends_color;
    }

    public function setShowDepends(bool $show_depends): self
    {
        $this->show_depends = $show_depends;

        return $this;
    }

    public function getShowDepends(): ?bool
    {
        return $this->show_depends;
    }

    public function setShowImpact(bool $show_impact): self
    {
        $this->show_impact = $show_impact;

        return $this;
    }

    public function getShowImpact(): ?bool
    {
        return $this->show_impact;
    }

    public function setMaxDepth(int $max_depth): self
    {
        $this->max_depth = $max_depth;

        return $this;
    }

    public function getMaxDepth(): ?int
    {
        return $this->max_depth;
    }
}
