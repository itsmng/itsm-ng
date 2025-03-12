<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_authldaps')]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "is_default", columns: ["is_default"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "sync_field", columns: ["sync_field"])]
class AuthLDAP
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
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
    private $locationField;

    #[ORM\Column(name: 'responsible_field', type: 'string', length: 255, nullable: true)]
    private $responsibleField;

    #[ORM\Column(name: 'pagesize', type: 'integer', options: ['default' => 0])]
    private $pagesize;

    #[ORM\Column(name: 'ldap_maxlimit', type: 'integer', options: ['default' => 0])]
    private $ldapMaxlimit;

    #[ORM\Column(name: 'can_support_pagesize', type: 'boolean', options: ['default' => false])]
    private $canSupportPagesize;

    #[ORM\Column(name: 'picture_field', type: 'string', length: 255, nullable: true)]
    private $pictureField;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'inventory_domain', type: 'string', length: 255, nullable: true)]
    private $inventoryDomain;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getBasedn(): string
    {
        return $this->basedn;
    }

    public function setBasedn(string $basedn): self
    {
        $this->basedn = $basedn;

        return $this;
    }

    public function getRootdn(): string
    {
        return $this->rootdn;
    }

    public function setRootdn(string $rootdn): self
    {
        $this->rootdn = $rootdn;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getLoginField(): string
    {
        return $this->loginField;
    }

    public function setLoginField(string $loginField): self
    {
        $this->loginField = $loginField;

        return $this;
    }

    public function getSyncField(): string
    {
        return $this->syncField;
    }

    public function setSyncField(string $syncField): self
    {
        $this->syncField = $syncField;

        return $this;
    }

    public function getUseTls(): bool
    {
        return $this->useTls;
    }

    public function setUseTls(bool $useTls): self
    {
        $this->useTls = $useTls;

        return $this;
    }

    public function getGroupField(): string
    {
        return $this->groupField;
    }

    public function setGroupField(string $groupField): self
    {
        $this->groupField = $groupField;

        return $this;
    }

    public function getGroupCondition(): string
    {
        return $this->groupCondition;
    }

    public function setGroupCondition(string $groupCondition): self
    {
        $this->groupCondition = $groupCondition;

        return $this;
    }

    public function getGroupSearchType(): int
    {
        return $this->groupSearchType;
    }

    public function setGroupSearchType(int $groupSearchType): self
    {
        $this->groupSearchType = $groupSearchType;

        return $this;
    }

    public function getGroupMemberField(): string
    {
        return $this->groupMemberField;
    }

    public function setGroupMemberField(string $groupMemberField): self
    {
        $this->groupMemberField = $groupMemberField;

        return $this;
    }

    public function getEmail1Field(): string
    {
        return $this->email1Field;
    }

    public function setEmail1Field(string $email1Field): self
    {
        $this->email1Field = $email1Field;

        return $this;
    }

    public function getRealnameField(): string
    {
        return $this->realnameField;
    }

    public function setRealnameField(string $realnameField): self
    {
        $this->realnameField = $realnameField;

        return $this;
    }

    public function getFirstnameField(): string
    {
        return $this->firstnameField;
    }

    public function setFirstnameField(string $firstnameField): self
    {
        $this->firstnameField = $firstnameField;

        return $this;
    }

    public function getPhoneField(): string
    {
        return $this->phoneField;
    }

    public function setPhoneField(string $phoneField): self
    {
        $this->phoneField = $phoneField;

        return $this;
    }

    public function getPhone2Field(): string
    {
        return $this->phone2Field;
    }

    public function setPhone2Field(string $phone2Field): self
    {
        $this->phone2Field = $phone2Field;

        return $this;
    }

    public function getMobileField(): string
    {
        return $this->mobileField;
    }

    public function setMobileField(string $mobileField): self
    {
        $this->mobileField = $mobileField;

        return $this;
    }

    public function getCommentField(): string
    {
        return $this->commentField;
    }

    public function setCommentField(string $commentField): self
    {
        $this->commentField = $commentField;

        return $this;
    }

    public function getUseDn(): bool
    {
        return $this->useDn;
    }

    public function setUseDn(bool $useDn): self
    {
        $this->useDn = $useDn;

        return $this;
    }

    public function getTimeOffset(): int
    {
        return $this->timeOffset;
    }

    public function setTimeOffset(int $timeOffset): self
    {
        $this->timeOffset = $timeOffset;

        return $this;
    }

    public function getDerefOptions(): int
    {
        return $this->derefOptions;
    }

    public function setDerefOptions(int $derefOptions): self
    {
        $this->derefOptions = $derefOptions;

        return $this;
    }

    public function getTitleField(): string
    {
        return $this->titleField;
    }

    public function setTitleField(string $titleField): self
    {
        $this->titleField = $titleField;

        return $this;
    }

    public function getCategoryField(): string
    {
        return $this->categoryField;
    }

    public function setCategoryField(string $categoryField): self
    {
        $this->categoryField = $categoryField;

        return $this;
    }

    public function getLanguageField(): string
    {
        return $this->languageField;
    }

    public function setLanguageField(string $languageField): self
    {
        $this->languageField = $languageField;

        return $this;
    }

    public function getEntityField(): string
    {
        return $this->entityField;
    }

    public function setEntityField(string $entityField): self
    {
        $this->entityField = $entityField;

        return $this;
    }

    public function getEntityCondition(): string
    {
        return $this->entityCondition;
    }

    public function setEntityCondition(string $entityCondition): self
    {
        $this->entityCondition = $entityCondition;

        return $this;
    }

    public function getDateMod(): \DateTime
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRootdnPasswd(): string
    {
        return $this->rootdnPasswd;
    }

    public function setRootdnPasswd(string $rootdnPasswd): self
    {
        $this->rootdnPasswd = $rootdnPasswd;

        return $this;
    }

    public function getRegistrationNumberField(): string
    {
        return $this->registrationNumberField;
    }

    public function setRegistrationNumberField(string $registrationNumberField): self
    {
        $this->registrationNumberField = $registrationNumberField;

        return $this;
    }

    public function getEmail2_field(): string
    {
        return $this->email2_field;
    }

    public function setEmail2_field(string $email2_field): self
    {
        $this->email2_field = $email2_field;

        return $this;
    }

    public function getEmail3_field(): string
    {
        return $this->email3_field;
    }

    public function setEmail3_field(string $email3_field): self
    {
        $this->email3_field = $email3_field;

        return $this;
    }

    public function getEmail4_field(): string
    {
        return $this->email4_field;
    }

    public function setEmail4_field(string $email4_field): self
    {
        $this->email4_field = $email4_field;

        return $this;
    }

    public function getLocationField(): string
    {
        return $this->locationField;
    }

    public function setLocationField(string $locationField): self
    {
        $this->locationField = $locationField;

        return $this;
    }

    public function getResponsibleField(): string
    {
        return $this->responsibleField;
    }

    public function setResponsibleField(string $responsibleField): self
    {
        $this->responsibleField = $responsibleField;

        return $this;
    }

    public function getPagesize(): int
    {
        return $this->pagesize;
    }

    public function setPagesize(int $pagesize): self
    {
        $this->pagesize = $pagesize;

        return $this;
    }

    public function getLdapMaxlimit(): int
    {
        return $this->ldapMaxlimit;
    }

    public function setLdapMaxlimit(int $ldapMaxlimit): self
    {
        $this->ldapMaxlimit = $ldapMaxlimit;

        return $this;
    }

    public function getCanSupportPagesize(): bool
    {
        return $this->canSupportPagesize;
    }

    public function setCanSupportPagesize(bool $canSupportPagesize): self
    {
        $this->canSupportPagesize = $canSupportPagesize;

        return $this;
    }

    public function getPictureField(): string
    {
        return $this->pictureField;
    }

    public function setPictureField(string $pictureField): self
    {
        $this->pictureField = $pictureField;

        return $this;
    }

    public function getDateCreation(): \DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getInventoryDomain(): string
    {
        return $this->inventoryDomain;
    }

    public function setInventoryDomain(string $inventoryDomain): self
    {
        $this->inventoryDomain = $inventoryDomain;

        return $this;
    }
}
