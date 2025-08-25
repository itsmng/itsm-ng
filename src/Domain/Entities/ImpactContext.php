<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_impactcontexts")]
class ImpactContext
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'positions', type: "text", length: 65535)]
    private $positions;

    #[ORM\Column(name: 'zoom', type: "float", options: ["default" => 0])]
    private $zoom;

    #[ORM\Column(name: 'pan_x', type: "float", options: ["default" => 0])]
    private $panX;

    #[ORM\Column(name: 'pan_y', type: "float", options: ["default" => 0])]
    private $panY;

    #[ORM\Column(name: 'impact_color', type: "string", length: 255, options: ["default" => ""])]
    private $impactColor;

    #[ORM\Column(name: 'depends_color', type: "string", length: 255, options: ["default" => ""])]
    private $dependsColor;

    #[ORM\Column(name: 'impact_and_depends_color', type: "string", length: 255, options: ["default" => ""])]
    private $impactAndDependsColor;

    #[ORM\Column(name: 'show_depends', type: "boolean", options: ["default" => true])]
    private $showDepends;

    #[ORM\Column(name: 'show_impact', type: "boolean", options: ["default" => true])]
    private $showImpact;

    #[ORM\Column(name: 'max_depth', type: "integer", options: ["default" => 5])]
    private $maxDepth;

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

    public function setPanX(float $panX): self
    {
        $this->panX = $panX;

        return $this;
    }

    public function getPanX(): ?float
    {
        return $this->panX;
    }

    public function setPanY(float $panY): self
    {
        $this->panY = $panY;

        return $this;
    }

    public function getPanY(): ?float
    {
        return $this->panY;
    }

    public function setImpactColor(string $impactColor): self
    {
        $this->impactColor = $impactColor;

        return $this;
    }

    public function getImpactColor(): ?string
    {
        return $this->impactColor;
    }

    public function setDependsColor(string $dependsColor): self
    {
        $this->dependsColor = $dependsColor;

        return $this;
    }

    public function getDependsColor(): ?string
    {
        return $this->dependsColor;
    }

    public function setImpactAndDependsColor(string $impactAndDependsColor): self
    {
        $this->impactAndDependsColor = $impactAndDependsColor;

        return $this;
    }

    public function getImpactAndDependsColor(): ?string
    {
        return $this->impactAndDependsColor;
    }

    public function setShowDepends(bool $showDepends): self
    {
        $this->showDepends = $showDepends;

        return $this;
    }

    public function getShowDepends(): ?bool
    {
        return $this->showDepends;
    }

    public function setShowImpact(bool $showImpact): self
    {
        $this->showImpact = $showImpact;

        return $this;
    }

    public function getShowImpact(): ?bool
    {
        return $this->showImpact;
    }

    public function setMaxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }
}
