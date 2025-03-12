<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
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
    private $isRecursive;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'ldap_field', type: 'string', length: 255, nullable: true)]
    private $ldapField;

    #[ORM\Column(name: 'ldap_value', type: 'text', nullable: true, length: 65535)]
    private $ldapValue;

    #[ORM\Column(name: 'ldap_group_dn', type: 'text', nullable: true, length: 65535)]
    private $ldapGroupDn;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'completename', type: 'text', nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(name: 'ancestors_cache', type: 'text', nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'sons_cache', type: 'text', nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'is_requester', type: 'boolean', options: ['default' => true])]
    private $isRequester;

    #[ORM\Column(name: 'is_watcher', type: 'boolean', options: ['default' => true])]
    private $isWatcher;

    #[ORM\Column(name: 'is_assign', type: 'boolean', options: ['default' => true])]
    private $isAssign;

    #[ORM\Column(name: 'is_task', type: 'boolean', options: ['default' => true])]
    private $isTask;

    #[ORM\Column(name: 'is_notify', type: 'boolean', options: ['default' => true])]
    private $isNotify;

    #[ORM\Column(name: 'is_itemgroup', type: 'boolean', options: ['default' => true])]
    private $isItemgroup;

    #[ORM\Column(name: 'is_usergroup', type: 'boolean', options: ['default' => true])]
    private $isUsergroup;

    #[ORM\Column(name: 'is_manager', type: 'boolean', options: ['default' => true])]
    private $isManager;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }


    /**
     * Get the value of groupKnowbaseItems
     */
    public function getGroupKnowbaseItems()
    {
        return $this->groupKnowbaseItems;
    }

    /**
     * Set the value of groupKnowbaseItems
     *
     * @return  self
     */
    public function setGroupKnowbaseItems($groupKnowbaseItems)
    {
        $this->groupKnowbaseItems = $groupKnowbaseItems;

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
    public function getGroupProblems()
    {
        return $this->groupProblems;
    }

    /**
     * Set the value of groupProblems
     *
     * @return  self
     */
    public function setGroupProblems($groupProblems)
    {
        $this->groupProblems = $groupProblems;

        return $this;
    }

    /**
     * Get the value of groupReminders
     */
    public function getGroupReminders()
    {
        return $this->groupReminders;
    }

    /**
     * Set the value of groupReminders
     *
     * @return  self
     */
    public function setGroupReminders($groupReminders)
    {
        $this->groupReminders = $groupReminders;

        return $this;
    }

    /**
     * Get the value of groupRSSFeeds
     */
    public function getGroupRSSFeeds()
    {
        return $this->groupRSSFeeds;
    }

    /**
     * Set the value of groupRSSFeeds
     *
     * @return  self
     */
    public function setGroupRSSFeeds($groupRSSFeeds)
    {
        $this->groupRSSFeeds = $groupRSSFeeds;

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
    public function getChangeGroups()
    {
        return $this->changeGroups;
    }

    /**
     * Set the value of changeGroups
     *
     * @return  self
     */
    public function setChangeGroups($changeGroups)
    {
        $this->changeGroups = $changeGroups;

        return $this;
    }

    /**
     * Get the value of groupUsers
     */
    public function getGroupUsers()
    {
        return $this->groupUsers;
    }

    /**
     * Set the value of groupUsers
     *
     * @return  self
     */
    public function setGroupUsers($groupUsers)
    {
        $this->groupUsers = $groupUsers;

        return $this;
    }
}
