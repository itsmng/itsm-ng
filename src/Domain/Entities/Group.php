<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_groups')]
#[ORM\Index(columns: ['name'])]
#[ORM\Index(columns: ['ldap_field'])]
#[ORM\Index(columns: ['entities_id'])]
#[ORM\Index(columns: ['date_mod'])]
#[ORM\Index(columns: ['ldap_value'], flags: ['fulltext'])]
#[ORM\Index(columns: ['ldap_group_dn'], flags: ['fulltext'])]
#[ORM\Index(columns: ['groups_id'])]
#[ORM\Index(columns: ['is_requester'])]
#[ORM\Index(columns: ['is_watcher'])]
#[ORM\Index(columns: ['is_assign'])]
#[ORM\Index(columns: ['is_notify'])]
#[ORM\Index(columns: ['is_itemgroup'])]
#[ORM\Index(columns: ['is_usergroup'])]
#[ORM\Index(columns: ['is_manager'])]
#[ORM\Index(columns: ['date_creation'])]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ldap_field;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $ldap_value;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $ldap_group_dn;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: 'text', nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_requester;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_watcher;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_assign;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_task;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_notify;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_itemgroup;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_usergroup;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_manager;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: ChangeGroup::class)]
    private Collection $changesGroups;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
        return $this->ldap_field;
    }

    public function setLdapField(?string $ldap_field): self
    {
        $this->ldap_field = $ldap_field;

        return $this;
    }

    public function getLdapValue(): ?string
    {
        return $this->ldap_value;
    }

    public function setLdapValue(?string $ldap_value): self
    {
        $this->ldap_value = $ldap_value;

        return $this;
    }

    public function getLdapGroupDn(): ?string
    {
        return $this->ldap_group_dn;
    }

    public function setLdapGroupDn(?string $ldap_group_dn): self
    {
        $this->ldap_group_dn = $ldap_group_dn;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getGroupsId(): ?Group
    {
        return $this->groups_id;
    }

    public function setGroupsId(?Group $groups_id): self
    {
        $this->groups_id = $groups_id;

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

    public function getIsRequester(): ?bool
    {
        return $this->is_requester;
    }

    public function setIsRequester(bool $is_requester): self
    {
        $this->is_requester = $is_requester;

        return $this;
    }

    public function getIsWatcher(): ?bool
    {
        return $this->is_watcher;
    }

    public function setIsWatcher(bool $is_watcher): self
    {
        $this->is_watcher = $is_watcher;

        return $this;
    }

    public function getIsAssign(): ?bool
    {
        return $this->is_assign;
    }

    public function setIsAssign(bool $is_assign): self
    {
        $this->is_assign = $is_assign;

        return $this;
    }

    public function getIsTask(): ?bool
    {
        return $this->is_task;
    }

    public function setIsTask(bool $is_task): self
    {
        $this->is_task = $is_task;

        return $this;
    }

    public function getIsNotify(): ?bool
    {
        return $this->is_notify;
    }

    public function setIsNotify(bool $is_notify): self
    {
        $this->is_notify = $is_notify;

        return $this;
    }

    public function getIsItemgroup(): ?bool
    {
        return $this->is_itemgroup;
    }

    public function setIsItemgroup(bool $is_itemgroup): self
    {
        $this->is_itemgroup = $is_itemgroup;

        return $this;
    }

    public function getIsUsergroup(): ?bool
    {
        return $this->is_usergroup;
    }

    public function setIsUsergroup(bool $is_usergroup): self
    {
        $this->is_usergroup = $is_usergroup;

        return $this;
    }

    public function getIsManager(): ?bool
    {
        return $this->is_manager;
    }

    public function setIsManager(bool $is_manager): self
    {
        $this->is_manager = $is_manager;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * Get the value of changesGroups
     */
    public function getChangesGroups()
    {
        return $this->changesGroups;
    }

    /**
     * Set the value of changesGroups
     *
     * @return  self
     */
    public function setChangesGroups($changesGroups)
    {
        $this->changesGroups = $changesGroups;

        return $this;
    }
}
