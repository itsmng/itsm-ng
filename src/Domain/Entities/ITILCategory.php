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
class ITILCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: ITILCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ITILCategory $itilCategory = null;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'completename', type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'level', type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\ManyToOne(targetEntity: KnowbaseItemCategory::class)]
    #[ORM\JoinColumn(name: 'knowbaseitemcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?KnowbaseItemCategory $knowbaseitemcategory = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'code', type: "string", length: 255, nullable: true)]
    private $code;

    #[ORM\Column(name: 'ancestors_cache', type: "text", nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'sons_cache', type: "text", nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'is_helpdeskvisible', type: "boolean", options: ["default" => 1])]
    private $isHelpdeskvisible;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id_incident', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplateIncident = null;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id_demand', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplateDemand = null;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ChangeTemplate $changetemplate = null;

    #[ORM\ManyToOne(targetEntity: ProblemTemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ProblemTemplate $problemtemplate = null;

    #[ORM\Column(name: 'is_incident', type: "integer", options: ["default" => 1])]
    private $isIncident;

    #[ORM\Column(name: 'is_request', type: "integer", options: ["default" => 1])]
    private $isRequest;

    #[ORM\Column(name: 'is_problem', type: "integer", options: ["default" => 1])]
    private $isProblem;

    #[ORM\Column(name: 'is_change', type: "boolean", options: ["default" => 1])]
    private $isChange;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function setCompleteName(?string $completeName): self
    {
        $this->completename = $completeName;

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
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(?string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sonsCache;
    }

    public function setSonsCache(?string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

        return $this;
    }

    public function getIsHelpdeskVisible(): ?bool
    {
        return $this->isHelpdeskvisible;
    }

    public function setIsHelpdeskVisible(?bool $isHelpdeskVisible): self
    {
        $this->isHelpdeskvisible = $isHelpdeskVisible;

        return $this;
    }

    public function getIsIncident(): ?bool
    {
        return $this->isIncident;
    }

    public function setIsIncident(?bool $isIncident): self
    {
        $this->isIncident = $isIncident;

        return $this;
    }

    public function getIsRequest(): ?bool
    {
        return $this->isRequest;
    }

    public function setIsRequest(?bool $isRequest): self
    {
        $this->isRequest = $isRequest;

        return $this;
    }

    public function getIsProblem(): ?bool
    {
        return $this->isProblem;
    }

    public function setIsProblem(?bool $isProblem): self
    {
        $this->isProblem = $isProblem;

        return $this;
    }

    public function getIsChange(): ?bool
    {
        return $this->isChange;
    }

    public function setIsChange(?bool $isChange): self
    {
        $this->isChange = $isChange;

        return $this;
    }

    public function getDateMod(): ?DateTime
    {
        return $this->dateMod;
    }

    public function setDateMod(?DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
    public function getITILCategory()
    {
        return $this->itilCategory;
    }

    /**
     * Set the value of itilCategory
     *
     * @return  self
     */
    public function setITILCategory($itilCategory)
    {
        $this->itilCategory = $itilCategory;

        return $this;
    }

    /**
     * Get the value of knowbaseitemcategory
     */
    public function getKnowbaseItemCategory()
    {
        return $this->knowbaseitemcategory;
    }

    /**
     * Set the value of knowbaseitemcategory
     *
     * @return  self
     */
    public function setKnowbaseItemCategory($knowbaseitemcategory)
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
