<?php

namespace GLPI\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_authldaps')]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "is_default", columns: ["is_default"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "sync_field", columns: ["sync_field"])]
class AuthLdaps {
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'host', type: 'string', length: 255, nullable: true)]
    private $host;

    #[ORM\Column(name: 'basedn', type: 'string', length: 255, nullable: true)]
    private $basedn;

    #[ORM\Column(name: 'rootdn', type: 'string', length: 255, nullable: true)]
    private $rootdn;

    #[ORM\Column(name: 'port', type: 'integer', options: ['default' => 389])]
    private $port;

    #[ORM\Column(name: 'condition', type: 'text', length: 65535, nullable: true)]
    private $condition;

    #[ORM\Column(name: 'login_field', type: 'string', length: 255, nullable: true, options: ['default' => 'uid'])]
    private $loginField;

    #[ORM\Column(name: 'sync_field', type: 'string', length: 255, nullable: true)]
    private $syncField;

    #[ORM\Column(name: 'use_tls', type: 'boolean', options: ['default' => false])]
    private $useTls;

    #[ORM\Column(name: 'group_field', type: 'string', length: 255, nullable: true)]
    private $groupField;

    #[ORM\Column(name: 'group_condition', type: 'text', length: 65535, nullable: true)]
    private $groupCondition;

    #[ORM\Column(name: 'group_search_type', type: 'integer', options: ['default' => 0])]
    private $groupSearchType;

    #[ORM\Column(name: 'group_member_field', type: 'string', length: 255, nullable: true)]
    private $groupMemberField;

    #[ORM\Column(name: 'email1_field', type: 'string', length: 255, nullable: true)]
    private $email1Field;

    #[ORM\Column(name: 'realname_field', type: 'string', length: 255, nullable: true)]
    private $realnameField;

    #[ORM\Column(name: 'firstname_field', type: 'string', length: 255, nullable: true)]
    private $firstnameField;

    #[ORM\Column(name: 'phone_field', type: 'string', length: 255, nullable: true)]
    private $phoneField;

    #[ORM\Column(name: 'phone2_field', type: 'string', length: 255, nullable: true)]
    private $phone2Field;

    #[ORM\Column(name: 'mobile_field', type: 'string', length: 255, nullable: true)]
    private $mobileField;

    #[ORM\Column(name: 'comment_field', type: 'string', length: 255, nullable: true)]
    private $commentField;

    #[ORM\Column(name: 'use_dn', type: 'boolean', options: ['default' => true])]
    private $useDn;

    #[ORM\Column(name: 'time_offset', type: 'integer', options: ['comment' => 'in seconds', 'default' => 0])]
    private $timeOffset;

    #[ORM\Column(name: 'deref_option', type: 'integer', options: ['default' => 0])]
    private $derefOptions;

    #[ORM\Column(name: 'title_field', type: 'string', length: 255, nullable: true)]
    private $titleField;

    #[ORM\Column(name: 'category_field', type: 'string', length: 255, nullable: true)]
    private $categoryField;

    #[ORM\Column(name: 'language_field', type: 'string', length: 255, nullable: true)]
    private $languageField;

    #[ORM\Column(name: 'entity_field', type: 'string', length: 255, nullable: true)]
    private $entityField;

    #[ORM\Column(name: 'entity_condition', type: 'text', length: 65535, nullable: true)]
    private $entityCondition;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'is_default', type: 'boolean', options: ['default' => false])]
    private $isDefault;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => false])]
    private $isActive;

    #[ORM\Column(name: 'rootdn_passwd', type: 'string', length: 255, nullable: true)]
    private $rootdnPasswd;

    #[ORM\Column(name: 'registration_number_field', type: 'string', length: 255, nullable: true)]
    private $registrationNumberField;

    #[ORM\Column(name: 'email2_field', type: 'string', length: 255, nullable: true)]
    private $email2_field;

    #[ORM\Column(name: 'email3_field', type: 'string', length: 255, nullable: true)]
    private $email3_field;

    #[ORM\Column(name: 'email4_field', type: 'string', length: 255, nullable: true)]
    private $email4_field;

    #[ORM\Column(name: 'location_field', type: 'string', length: 255, nullable: true)]
    private $location_field;

    #[ORM\Column(name: 'responsible_field', type: 'string', length: 255, nullable: true)]
    private $responsible_field;

    #[ORM\Column(name: 'pagesize', type: 'integer', options: ['default' => 0])]
    private $pagesize;

    #[ORM\Column(name: 'ldap_maxlimit', type: 'integer', options: ['default' => 0])]
    private $ldap_maxlimit;

    #[ORM\Column(name: 'can_support_pagesize', type: 'boolean', options: ['default' => false])]
    private $can_support_pagesize;

    #[ORM\Column(name: 'picture_field', type: 'string', length: 255, nullable: true)]
    private $picture_field;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(name: 'inventory_domain', type: 'string', length: 255, nullable: true)]
    private $inventory_domain;

    function getId(): int
    {
        return $this->id;
    }

    function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    function getName(): string
    {
        return $this->name;
    }

    function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    function getHost(): string
    {
        return $this->host;
    }

    function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    function getBasedn(): string
    {
        return $this->basedn;
    }

    function setBasedn(string $basedn): self
    {
        $this->basedn = $basedn;

        return $this;
    }

    function getRootdn(): string
    {
        return $this->rootdn;
    }

    function setRootdn(string $rootdn): self
    {
        $this->rootdn = $rootdn;

        return $this;
    }

    function getPort(): int
    {
        return $this->port;
    }

    function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    function getCondition(): string
    {
        return $this->condition;
    }

    function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    function getLoginField(): string
    {
        return $this->loginField;
    }

    function setLoginField(string $loginField): self
    {
        $this->loginField = $loginField;

        return $this;
    }

    function getSyncField(): string
    {
        return $this->syncField;
    }

    function setSyncField(string $syncField): self
    {
        $this->syncField = $syncField;

        return $this;
    }

    function getUseTls(): bool
    {
        return $this->useTls;
    }

    function setUseTls(bool $useTls): self
    {
        $this->useTls = $useTls;

        return $this;
    }

    function getGroupField(): string
    {
        return $this->groupField;
    }

    function setGroupField(string $groupField): self
    {
        $this->groupField = $groupField;

        return $this;
    }

    function getGroupCondition(): string
    {
        return $this->groupCondition;
    }

    function setGroupCondition(string $groupCondition): self
    {
        $this->groupCondition = $groupCondition;

        return $this;
    }

    function getGroupSearchType(): int
    {
        return $this->groupSearchType;
    }

    function setGroupSearchType(int $groupSearchType): self
    {
        $this->groupSearchType = $groupSearchType;

        return $this;
    }

    function getGroupMemberField(): string
    {
        return $this->groupMemberField;
    }

    function setGroupMemberField(string $groupMemberField): self
    {
        $this->groupMemberField = $groupMemberField;

        return $this;
    }

    function getEmail1Field(): string
    {
        return $this->email1Field;
    }

    function setEmail1Field(string $email1Field): self
    {
        $this->email1Field = $email1Field;

        return $this;
    }

    function getRealnameField(): string
    {
        return $this->realnameField;
    }

    function setRealnameField(string $realnameField): self
    {
        $this->realnameField = $realnameField;

        return $this;
    }

    function getFirstnameField(): string
    {
        return $this->firstnameField;
    }

    function setFirstnameField(string $firstnameField): self
    {
        $this->firstnameField = $firstnameField;

        return $this;
    }

    function getPhoneField(): string
    {
        return $this->phoneField;
    }

    function setPhoneField(string $phoneField): self
    {
        $this->phoneField = $phoneField;

        return $this;
    }

    function getPhone2Field(): string
    {
        return $this->phone2Field;
    }

    function setPhone2Field(string $phone2Field): self
    {
        $this->phone2Field = $phone2Field;

        return $this;
    }

    function getMobileField(): string
    {
        return $this->mobileField;
    }

    function setMobileField(string $mobileField): self
    {
        $this->mobileField = $mobileField;

        return $this;
    }

    function getCommentField(): string
    {
        return $this->commentField;
    }

    function setCommentField(string $commentField): self
    {
        $this->commentField = $commentField;

        return $this;
    }

    function getUseDn(): bool
    {
        return $this->useDn;
    }

    function setUseDn(bool $useDn): self
    {
        $this->useDn = $useDn;

        return $this;
    }

    function getTimeOffset(): int
    {
        return $this->timeOffset;
    }

    function setTimeOffset(int $timeOffset): self
    {
        $this->timeOffset = $timeOffset;

        return $this;
    }

    function getDerefOptions(): int
    {
        return $this->derefOptions;
    }

    function setDerefOptions(int $derefOptions): self
    {
        $this->derefOptions = $derefOptions;

        return $this;
    }

    function getTitleField(): string
    {
        return $this->titleField;
    }

    function setTitleField(string $titleField): self
    {
        $this->titleField = $titleField;

        return $this;
    }

    function getCategoryField(): string
    {
        return $this->categoryField;
    }

    function setCategoryField(string $categoryField): self
    {
        $this->categoryField = $categoryField;

        return $this;
    }

    function getLanguageField(): string
    {
        return $this->languageField;
    }

    function setLanguageField(string $languageField): self
    {
        $this->languageField = $languageField;

        return $this;
    }

    function getEntityField(): string
    {
        return $this->entityField;
    }

    function setEntityField(string $entityField): self
    {
        $this->entityField = $entityField;

        return $this;
    }

    function getEntityCondition(): string
    {
        return $this->entityCondition;
    }

    function setEntityCondition(string $entityCondition): self
    {
        $this->entityCondition = $entityCondition;

        return $this;
    }

    function getDateMod(): \DateTime
    {
        return $this->dateMod;
    }

    function setDateMod(\DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    function getComment(): string
    {
        return $this->comment;
    }

    function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    function getIsActive(): bool
    {
        return $this->isActive;
    }

    function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    function getRootdnPasswd(): string
    {
        return $this->rootdnPasswd;
    }

    function setRootdnPasswd(string $rootdnPasswd): self
    {
        $this->rootdnPasswd = $rootdnPasswd;

        return $this;
    }

    function getRegistrationNumberField(): string
    {
        return $this->registrationNumberField;
    }

    function setRegistrationNumberField(string $registrationNumberField): self
    {
        $this->registrationNumberField = $registrationNumberField;

        return $this;
    }

    function getEmail2_field(): string
    {
        return $this->email2_field;
    }

    function setEmail2_field(string $email2_field): self
    {
        $this->email2_field = $email2_field;

        return $this;
    }

    function getEmail3_field(): string
    {
        return $this->email3_field;
    }

    function setEmail3_field(string $email3_field): self
    {
        $this->email3_field = $email3_field;

        return $this;
    }

    function getEmail4_field(): string
    {
        return $this->email4_field;
    }

    function setEmail4_field(string $email4_field): self
    {
        $this->email4_field = $email4_field;

        return $this;
    }

    function getLocation_field(): string
    {
        return $this->location_field;
    }

    function setLocation_field(string $location_field): self
    {
        $this->location_field = $location_field;

        return $this;
    }

    function getResponsible_field(): string
    {
        return $this->responsible_field;
    }

    function setResponsible_field(string $responsible_field): self
    {
        $this->responsible_field = $responsible_field;

        return $this;
    }

    function getPagesize(): int
    {
        return $this->pagesize;
    }

    function setPagesize(int $pagesize): self
    {
        $this->pagesize = $pagesize;

        return $this;
    }

    function getLdap_maxlimit(): int
    {
        return $this->ldap_maxlimit;
    }

    function setLdap_maxlimit(int $ldap_maxlimit): self
    {
        $this->ldap_maxlimit = $ldap_maxlimit;

        return $this;
    }

    function getCan_support_pagesize(): bool
    {
        return $this->can_support_pagesize;
    }

    function setCan_support_pagesize(bool $can_support_pagesize): self
    {
        $this->can_support_pagesize = $can_support_pagesize;

        return $this;
    }

    function getPicture_field(): string
    {
        return $this->picture_field;
    }

    function setPicture_field(string $picture_field): self
    {
        $this->picture_field = $picture_field;

        return $this;
    }

    function getDate_creation(): \DateTime
    {
        return $this->date_creation;
    }

    function setDate_creation(\DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    function getInventory_domain(): string
    {
        return $this->inventory_domain;
    }

    function setInventory_domain(string $inventory_domain): self
    {
        $this->inventory_domain = $inventory_domain;

        return $this;
    }
}
