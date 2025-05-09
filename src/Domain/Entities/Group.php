<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_groups')]
#[ORM\Index(name: "name", columns: ['name'])]
#[ORM\Index(name: "ldap_field", columns: ['ldap_field'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "ldap_value", columns: ['ldap_value'], flags: ['fulltext'])]
#[ORM\Index(name: "ldap_group_dn", columns: ['ldap_group_dn'], flags: ['fulltext'])]
#[ORM\Index(name: "groups_id", columns: ['groups_id'])]
#[ORM\Index(name: "is_requester", columns: ['is_requester'])]
#[ORM\Index(name: "is_watcher", columns: ['is_watcher'])]
#[ORM\Index(name: "is_assign", columns: ['is_assign'])]
#[ORM\Index(name: "is_notify", columns: ['is_notify'])]
#[ORM\Index(name: "is_itemgroup", columns: ['is_itemgroup'])]
#[ORM\Index(name: "is_usergroup", columns: ['is_usergroup'])]
#[ORM\Index(name: "is_manager", columns: ['is_manager'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'entityRSSFeeds')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive = false;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name = null;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment = null;

    #[ORM\Column(name: 'ldap_field', type: 'string', length: 255, nullable: true)]
    private $ldapField = null;

    #[ORM\Column(name: 'ldap_value', type: 'text', nullable: true, length: 65535)]
    private $ldapValue = null;

    #[ORM\Column(name: 'ldap_group_dn', type: 'text', nullable: true, length: 65535)]
    private $ldapGroupDn = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'completename', type: 'text', nullable: true, length: 65535)]
    private $completename = null;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level = 0;

    #[ORM\Column(name: 'ancestors_cache', type: 'text', nullable: true)]
    private $ancestorsCache = null;

    #[ORM\Column(name: 'sons_cache', type: 'text', nullable: true)]
    private $sonsCache = null;

    #[ORM\Column(name: 'is_requester', type: 'boolean', options: ['default' => true])]
    private $isRequester = true;

    #[ORM\Column(name: 'is_watcher', type: 'boolean', options: ['default' => true])]
    private $isWatcher = true;

    #[ORM\Column(name: 'is_assign', type: 'boolean', options: ['default' => true])]
    private $isAssign = true;

    #[ORM\Column(name: 'is_task', type: 'boolean', options: ['default' => true])]
    private $isTask = true;

    #[ORM\Column(name: 'is_notify', type: 'boolean', options: ['default' => true])]
    private $isNotify = true;

    #[ORM\Column(name: 'is_itemgroup', type: 'boolean', options: ['default' => true])]
    private $isItemgroup = true;

    #[ORM\Column(name: 'is_usergroup', type: 'boolean', options: ['default' => true])]
    private $isUsergroup = true;

    #[ORM\Column(name: 'is_manager', type: 'boolean', options: ['default' => true])]
    private $isManager = true;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: ChangeGroup::class)]
    private Collection $changeGroups;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupKnowbaseItem::class)]
    private Collection $groupKnowbaseItems;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupProblem::class)]
    private Collection $groupProblems;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupReminder::class)]
    private Collection $groupReminders;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupRSSFeed::class)]
    private Collection $groupRSSFeeds;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupTicket::class)]
    private Collection $groupTickets;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupUser::class)]
    private Collection $groupUsers;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getLdapField(): ?string
    {
        return $this->ldapField;
    }

    public function setLdapField(?string $ldapField): self
    {
        $this->ldapField = $ldapField;

        return $this;
    }

    public function getLdapValue(): ?string
    {
        return $this->ldapValue;
    }

    public function setLdapValue(?string $ldapValue): self
    {
        $this->ldapValue = $ldapValue;

        return $this;
    }

    public function getLdapGroupDn(): ?string
    {
        return $this->ldapGroupDn;
    }

    public function setLdapGroupDn(?string $ldapGroupDn): self
    {
        $this->ldapGroupDn = $ldapGroupDn;

        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(?string $completename): self
    {
        $this->completename = $completename;

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

    public function getIsRequester(): ?bool
    {
        return $this->isRequester;
    }

    public function setIsRequester(bool $isRequester): self
    {
        $this->isRequester = $isRequester;

        return $this;
    }

    public function getIsWatcher(): ?bool
    {
        return $this->isWatcher;
    }

    public function setIsWatcher(bool $isWatcher): self
    {
        $this->isWatcher = $isWatcher;

        return $this;
    }

    public function getIsAssign(): ?bool
    {
        return $this->isAssign;
    }

    public function setIsAssign(bool $isAssign): self
    {
        $this->isAssign = $isAssign;

        return $this;
    }

    public function getIsTask(): ?bool
    {
        return $this->isTask;
    }

    public function setIsTask(bool $isTask): self
    {
        $this->isTask = $isTask;

        return $this;
    }

    public function getIsNotify(): ?bool
    {
        return $this->isNotify;
    }

    public function setIsNotify(bool $isNotify): self
    {
        $this->isNotify = $isNotify;

        return $this;
    }

    public function getIsItemgroup(): ?bool
    {
        return $this->isItemgroup;
    }

    public function setIsItemgroup(bool $isItemgroup): self
    {
        $this->isItemgroup = $isItemgroup;

        return $this;
    }

    public function getIsUsergroup(): ?bool
    {
        return $this->isUsergroup;
    }

    public function setIsUsergroup(bool $isUsergroup): self
    {
        $this->isUsergroup = $isUsergroup;

        return $this;
    }

    public function getIsManager(): ?bool
    {
        return $this->isManager;
    }

    public function setIsManager(bool $isManager): self
    {
        $this->isManager = $isManager;

        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }


    /**
     * Get the value of groupKnowbaseItems
     */
    public function getGroupKnowbaseItems(): Collection
    {
        if (!isset($this->groupKnowbaseItems)) {
            $this->groupKnowbaseItems = new ArrayCollection();
        }
        return $this->groupKnowbaseItems;
    }

    /**
     * Set the value of groupKnowbaseItems
     *
     * @return  self
     */
    public function setGroupKnowbaseItems(?Collection $groupKnowbaseItems)
    {
        $this->groupKnowbaseItems = $groupKnowbaseItems ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of entity
     */
    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function getEntityId(): int
    {
        return $this->entity ? $this->entity->getId() : -1;
    }
    /**
     * Set the value of entity
     *
     * @param Entity|null $entity
     * @return self
     */
    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;
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
     * Get the value of groupProblems
     */
    public function getGroupProblems(): Collection
    {
        if (!isset($this->groupProblems)) {
            $this->groupProblems = new ArrayCollection();
        }
        return $this->groupProblems;
    }


    /**
     * Set the value of groupProblems
     *
     * @return  self
     */
    public function setGroupProblems(?Collection $groupProblems): self
    {
        $this->groupProblems = $groupProblems ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of groupReminders
     */
    public function getGroupReminders(): Collection
    {
        if (!isset($this->groupReminders)) {
            $this->groupReminders = new ArrayCollection();
        }
        return $this->groupReminders;
    }

    /**
     * Set the value of groupReminders
     *
     * @return  self
     */
    public function setGroupReminders(?Collection $groupReminders): self
    {
        $this->groupReminders = $groupReminders ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of groupRSSFeeds
     */
    public function getGroupRSSFeeds(): Collection
    {
        if (!isset($this->groupRSSFeeds)) {
            $this->groupRSSFeeds = new ArrayCollection();
        }
        return $this->groupRSSFeeds;
    }

    /**
     * Set the value of groupRSSFeeds
     *
     * @return  self
     */
    public function setGroupRSSFeeds(?Collection $groupRSSFeeds): self
    {
        $this->groupRSSFeeds = $groupRSSFeeds ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of groupTickets
     */
    public function getGroupTickets()
    {
        return $this->groupTickets;
    }

    /**
     * Set the value of groupTickets
     *
     * @return  self
     */
    public function setGroupTickets($groupTickets)
    {
        $this->groupTickets = $groupTickets;

        return $this;
    }

    /**
     * Get the value of changeGroups
     */
    public function getChangeGroups(): Collection
    {
        if (!isset($this->changeGroups)) {
            $this->changeGroups = new ArrayCollection();
        }
        return $this->changeGroups;
    }

    /**
     * Set the value of changeGroups
     *
     * @return  self
     */
    public function setChangeGroups(?Collection $changeGroups)
    {
        $this->changeGroups = $changeGroups ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of groupUsers
     */
    public function getGroupUsers(): Collection
    {
        if (!isset($this->groupUsers)) {
            $this->groupUsers = new ArrayCollection();
        }
        return $this->groupUsers;
    }

    /**
     * Set the value of groupUsers
     *
     * @return  self
     */
    public function setGroupUsers(?Collection $groupUsers): self
    {
        $this->groupUsers = $groupUsers ?? new ArrayCollection();

        return $this;
    }
}
