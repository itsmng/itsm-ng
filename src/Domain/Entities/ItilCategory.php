<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_itilcategories")]
#[ORM\Index(columns: ["name"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
#[ORM\Index(columns: ["knowbaseitemcategories_id"])]
#[ORM\Index(columns: ["users_id"])]
#[ORM\Index(columns: ["groups_id"])]
#[ORM\Index(columns: ["is_helpdeskvisible"])]
#[ORM\Index(columns: ["itilcategories_id"])]
#[ORM\Index(columns: ["tickettemplates_id_incident"])]
#[ORM\Index(columns: ["tickettemplates_id_demand"])]
#[ORM\Index(columns: ["changetemplates_id"])]
#[ORM\Index(columns: ["problemtemplates_id"])]
#[ORM\Index(columns: ["is_incident"])]
#[ORM\Index(columns: ["is_request"])]
#[ORM\Index(columns: ["is_problem"])]
#[ORM\Index(columns: ["is_change"])]
#[ORM\Index(columns: ["date_mod"])]
#[ORM\Index(columns: ["date_creation"])]
class ItilCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $itilcategories_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $knowbaseitemcategories_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $users_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $groups_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $code;

    #[ORM\Column(type: "text", nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: "text", nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $is_helpdeskvisible;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $tickettemplates_id_incident;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $tickettemplates_id_demand;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $changetemplates_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $problemtemplates_id;

    #[ORM\Column(type: "integer", options: ["default" => 1])]
    private $is_incident;

    #[ORM\Column(type: "integer", options: ["default" => 1])]
    private $is_request;

    #[ORM\Column(type: "integer", options: ["default" => 1])]
    private $is_problem;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $is_change;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItilCategoriesId(): ?int
    {
        return $this->itilcategories_id;
    }

    public function setItilCategoriesId(?int $itilcategories_id): self
    {
        $this->itilcategories_id = $itilcategories_id;

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

    public function getCompleteName(): ?string
    {
        return $this->completename;
    }

    public function setCompleteName(?string $complete_name): self
    {
        $this->completename = $complete_name;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getKnowbaseitemcategoriesId(): ?int
    {
        return $this->knowbaseitemcategories_id;
    }

    public function setKnowbaseitemcategoriesId(?int $knowbaseitemcategories_id): self
    {
        $this->knowbaseitemcategories_id = $knowbaseitemcategories_id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->users_id;
    }

    public function setUserId(?int $user_id): self
    {
        $this->users_id = $user_id;

        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupId(?int $group_id): self
    {
        $this->groups_id = $group_id;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestors_cache;
    }

    public function setAncestorsCache(?string $ancestors_cache): self
    {
        $this->ancestors_cache = $ancestors_cache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sons_cache;
    }

    public function setSonsCache(?string $sons_cache): self
    {
        $this->sons_cache = $sons_cache;

        return $this;
    }

    public function getIsHelpdeskVisible(): ?bool
    {
        return $this->is_helpdeskvisible;
    }

    public function setIsHelpdeskVisible(?bool $is_helpdesk_visible): self
    {
        $this->is_helpdeskvisible = $is_helpdesk_visible;

        return $this;
    }

    public function getTickettemplatesIdIncident(): ?int
    {
        return $this->tickettemplates_id_incident;
    }

    public function setTickettemplatesIdIncident(?int $tickettemplates_id_incident): self
    {
        $this->tickettemplates_id_incident = $tickettemplates_id_incident;

        return $this;
    }

    public function getTickettemplatesIdDemand(): ?int
    {
        return $this->tickettemplates_id_demand;
    }

    public function setTickettemplatesIdDemand(?int $tickettemplates_id_demand): self
    {
        $this->tickettemplates_id_demand = $tickettemplates_id_demand;

        return $this;
    }

    public function getChangetemplatesId(): ?int
    {
        return $this->changetemplates_id;
    }

    public function setChangetemplatesId(?int $changetemplates_id): self
    {
        $this->changetemplates_id = $changetemplates_id;

        return $this;
    }

    public function getProblemtemplatesId(): ?int
    {
        return $this->problemtemplates_id;
    }

    public function setProblemtemplatesId(?int $problemtemplates_id): self
    {
        $this->problemtemplates_id = $problemtemplates_id;

        return $this;
    }

    public function getIsIncident(): ?bool
    {
        return $this->is_incident;
    }

    public function setIsIncident(?bool $is_incident): self
    {
        $this->is_incident = $is_incident;

        return $this;
    }

    public function getIsRequest(): ?bool
    {
        return $this->is_request;
    }

    public function setIsRequest(?bool $is_request): self
    {
        $this->is_request = $is_request;

        return $this;
    }

    public function getIsProblem(): ?bool
    {
        return $this->is_problem;
    }

    public function setIsProblem(?bool $is_problem): self
    {
        $this->is_problem = $is_problem;

        return $this;
    }

    public function getIsChange(): ?bool
    {
        return $this->is_change;
    }

    public function setIsChange(?bool $is_change): self
    {
        $this->is_change = $is_change;

        return $this;
    }

    public function getDateMod(): ?DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
