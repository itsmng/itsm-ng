<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_itilcategories")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "knowbaseitemcategories_id", columns: ["knowbaseitemcategories_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "is_helpdeskvisible", columns: ["is_helpdeskvisible"])]
#[ORM\Index(name: "itilcategories_id", columns: ["itilcategories_id"])]
#[ORM\Index(name: "tickettemplates_id_incident", columns: ["tickettemplates_id_incident"])]
#[ORM\Index(name: "tickettemplates_id_demand", columns: ["tickettemplates_id_demand"])]
#[ORM\Index(name: "changetemplates_id", columns: ["changetemplates_id"])]
#[ORM\Index(name: "problemtemplates_id", columns: ["problemtemplates_id"])]
#[ORM\Index(name: "is_incident", columns: ["is_incident"])]
#[ORM\Index(name: "is_request", columns: ["is_request"])]
#[ORM\Index(name: "is_problem", columns: ["is_problem"])]
#[ORM\Index(name: "is_change", columns: ["is_change"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class ItilCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: 'entities_id', options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "integer", name: 'itilcategories_id', options: ["default" => 0])]
    private $itilcategories_id;

    #[ORM\ManyToOne(targetEntity: ItilCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: false)]
    private ?ItilCategory $itilCategory;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\Column(type: "integer", name: 'knowbaseitemcategories_id', options: ["default" => 0])]
    private $knowbaseitemcategories_id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitemcategory::class)]
    #[ORM\JoinColumn(name: 'knowbaseitemcategories_id', referencedColumnName: 'id', nullable: false)]
    private ?Knowbaseitemcategory $knowbaseitemcategory;

    #[ORM\Column(type: "integer", name: 'users_id', options: ["default" => 0])]
    private $users_id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user;

    #[ORM\Column(type: "integer", name: 'groups_id', options: ["default" => 0])]
    private $groups_id;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group;

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

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id_incident', referencedColumnName: 'id', nullable: false)]
    private ?TicketTemplate $tickettemplateIncident;

    #[ORM\Column(type: "integer", name: 'tickettemplates_id_demand', options: ["default" => 0])]
    private $tickettemplates_id_demand;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id_demand', referencedColumnName: 'id', nullable: false)]
    private ?TicketTemplate $tickettemplateDemand;

    #[ORM\Column(type: "integer", name: 'changetemplates_id', options: ["default" => 0])]
    private $changetemplates_id;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?ChangeTemplate $changetemplate;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $problemtemplates_id;

    #[ORM\ManyToOne(targetEntity: ProblemTemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?ProblemTemplate $problemtemplate;

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
     * Get the value of itilCategory
     */
    public function getItilCategory()
    {
        return $this->itilCategory;
    }

    /**
     * Set the value of itilCategory
     *
     * @return  self
     */
    public function setItilCategory($itilCategory)
    {
        $this->itilCategory = $itilCategory;

        return $this;
    }

    /**
     * Get the value of knowbaseitemcategory
     */
    public function getKnowbaseitemcategory()
    {
        return $this->knowbaseitemcategory;
    }

    /**
     * Set the value of knowbaseitemcategory
     *
     * @return  self
     */
    public function setKnowbaseitemcategory($knowbaseitemcategory)
    {
        $this->knowbaseitemcategory = $knowbaseitemcategory;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get the value of tickettemplateIncident
     */
    public function getTickettemplateIncident()
    {
        return $this->tickettemplateIncident;
    }

    /**
     * Set the value of tickettemplateIncident
     *
     * @return  self
     */
    public function setTickettemplateIncident($tickettemplateIncident)
    {
        $this->tickettemplateIncident = $tickettemplateIncident;

        return $this;
    }

    /**
     * Get the value of tickettemplateDemand
     */
    public function getTickettemplateDemand()
    {
        return $this->tickettemplateDemand;
    }

    /**
     * Set the value of tickettemplateDemand
     *
     * @return  self
     */
    public function setTickettemplateDemand($tickettemplateDemand)
    {
        $this->tickettemplateDemand = $tickettemplateDemand;

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

    /**
     * Get the value of problemtemplate
     */
    public function getProblemtemplate()
    {
        return $this->problemtemplate;
    }

    /**
     * Set the value of problemtemplate
     *
     * @return  self
     */
    public function setProblemtemplate($problemtemplate)
    {
        $this->problemtemplate = $problemtemplate;

        return $this;
    }
}
