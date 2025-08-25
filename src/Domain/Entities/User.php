<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Itsmng\Domain\Entities\RequestType as EntitiesRequestType;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "glpi_users")]
#[ORM\UniqueConstraint(name: "unicityloginauth", columns: ["name", "authtype", "auths_id"])]
#[ORM\Index(name: "firstname", columns: ["firstname"])]
#[ORM\Index(name: "realname", columns: ["realname"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "profiles_id", columns: ["profiles_id"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "usertitles_id", columns: ["usertitles_id"])]
#[ORM\Index(name: "usercategories_id", columns: ["usercategories_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "authitem", columns: ["authtype", "auths_id"])]
#[ORM\Index(name: "is_deleted_ldap", columns: ["is_deleted_ldap"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "begin_date", columns: ["begin_date"])]
#[ORM\Index(name: "end_date", columns: ["end_date"])]
#[ORM\Index(name: "sync_field", columns: ["sync_field"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "users_id_supervisor", columns: ["users_id_supervisor"])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name = null;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: true)]
    private $password = null;

    #[ORM\Column(name: 'password_last_update', type: 'datetime', nullable: true)]
    private $passwordLastUpdate = null;

    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    private $phone = null;

    #[ORM\Column(name: 'phone2', type: 'string', length: 255, nullable: true)]
    private $phone2 = null;

    #[ORM\Column(name: 'mobile', type: 'string', length: 255, nullable: true)]
    private $mobile = null;

    #[ORM\Column(name: 'realname', type: 'string', length: 255, nullable: true)]
    private $realname = null;

    #[ORM\Column(name: 'firstname', type: 'string', length: 255, nullable: true)]
    private $firstname = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\Column(name: 'language', type: 'string', length: 10, nullable: true, options: ['comment' => 'see define.php CFG_GLPI[language] array'])]
    private $language;

    #[ORM\Column(name: 'use_mode', type: 'integer', options: ['default' => 0])]
    private $useMode = 0;

    #[ORM\Column(name: 'list_limit', type: 'integer', nullable: true)]
    private $listLimit;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 1])]
    private $isActive = 1;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'auths_id', type: 'integer', options: ['default' => 0])]
    private $authsId = 0;

    #[ORM\Column(name: 'authtype', type: 'integer', options: ['default' => 0])]
    private $authtype = 0;

    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    private $lastLogin;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'date_sync', type: 'datetime', nullable: true)]
    private $dateSync;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\ManyToOne(targetEntity: Profile::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'profiles_id', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne(targetEntity: Usertitle::class)]
    #[ORM\JoinColumn(name: 'usertitles_id', referencedColumnName: 'id', nullable: true)]
    private ?Usertitle $usertitle = null;

    #[ORM\ManyToOne(targetEntity: Usercategory::class)]
    #[ORM\JoinColumn(name: 'usercategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Usercategory $usercategory = null;

    #[ORM\Column(name: 'date_format', type: 'integer', nullable: true)]
    private $dateFormat;

    #[ORM\Column(name: 'number_format', type: 'integer', nullable: true)]
    private $numberFormat;

    #[ORM\Column(name: 'names_format', type: 'integer', nullable: true)]
    private $namesFormat;

    #[ORM\Column(name: 'csv_delimiter', type: 'string', length: 1, nullable: true)]
    private $csvDelimiter;

    #[ORM\Column(name: 'is_ids_visible', type: 'boolean', nullable: true)]
    private $isIdsVisible;

    #[ORM\Column(name: 'use_flat_dropdowntree', type: 'boolean', nullable: true)]
    private $useFlatDropdowntree;

    #[ORM\Column(name: 'show_jobs_at_login', type: 'boolean', nullable: true)]
    private $showJobsAtLogin;

    #[ORM\Column(name: 'priority_1', type: 'string', length: 20, nullable: true)]
    private $priority1;

    #[ORM\Column(name: 'priority_2', type: 'string', length: 20, nullable: true)]
    private $priority2;

    #[ORM\Column(name: 'priority_3', type: 'string', length: 20, nullable: true)]
    private $priority3;

    #[ORM\Column(name: 'priority_4', type: 'string', length: 20, nullable: true)]
    private $priority4;

    #[ORM\Column(name: 'priority_5', type: 'string', length: 20, nullable: true)]
    private $priority5;

    #[ORM\Column(name: 'priority_6', type: 'string', length: 20, nullable: true)]
    private $priority6;

    #[ORM\Column(name: 'followup_private', type: 'boolean', nullable: true)]
    private $followupPrivate;

    #[ORM\Column(name: 'task_private', type: 'boolean', nullable: true)]
    private $taskPrivate;

    #[ORM\ManyToOne(targetEntity: EntitiesRequestType::class)]
    #[ORM\JoinColumn(name: 'default_requesttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?EntitiesRequestType $defaultRequesttype = null;

    #[ORM\Column(name: 'password_forget_token', type: 'string', length: 40, nullable: true)]
    private $passwordForgetToken;

    #[ORM\Column(name: 'password_forget_token_date', type: 'datetime', nullable: true)]
    private $passwordForgetTokenDate;

    #[ORM\Column(name: 'user_dn', type: 'text', length: 65535, nullable: true)]
    private $userDn;

    #[ORM\Column(name: 'registration_number', type: 'string', length: 255, nullable: true)]
    private $registrationNumber;

    #[ORM\Column(name: 'show_count_on_tabs', type: 'boolean', nullable: true)]
    private $showCountOnTabs;

    #[ORM\Column(name: 'refresh_views', type: 'integer', nullable: true)]
    private $refreshViews;

    #[ORM\Column(name: 'set_default_tech', type: 'boolean', nullable: true)]
    private $setDefaultTech;

    #[ORM\Column(name: 'personal_token', type: 'string', length: 255, nullable: true)]
    private $personalToken;

    #[ORM\Column(name: 'personal_token_date', type: 'datetime', nullable: true)]
    private $personalTokenDate;

    #[ORM\Column(name: 'api_token', type: 'string', length: 255, nullable: true)]
    private $apiToken;

    #[ORM\Column(name: 'api_token_date', type: 'datetime', nullable: true)]
    private $apiTokenDate;

    #[ORM\Column(name: 'cookie_token', type: 'string', length: 255, nullable: true)]
    private $cookieToken;

    #[ORM\Column(name: 'cookie_token_date', type: 'datetime', nullable: true)]
    private $cookieTokenDate;

    #[ORM\Column(name: 'display_count_on_home', type: 'integer', nullable: true)]
    private $displayCountOnHome;

    #[ORM\Column(name: 'notification_to_myself', type: 'boolean', nullable: true)]
    private $notificationToMyself;

    #[ORM\Column(name: 'duedateok_color', type: 'string', length: 255, nullable: true)]
    private $duedateokColor;

    #[ORM\Column(name: 'duedatewarning_color', type: 'string', length: 255, nullable: true)]
    private $duedatewarningColor;

    #[ORM\Column(name: 'duedatecritical_color', type: 'string', length: 255, nullable: true)]
    private $duedatecriticalColor;

    #[ORM\Column(name: 'duedatewarning_less', type: 'integer', nullable: true)]
    private $duedatewarningLess;

    #[ORM\Column(name: 'duedatecritical_less', type: 'integer', nullable: true)]
    private $duedatecriticalLess;

    #[ORM\Column(name: 'duedatewarning_unit', type: 'string', length: 255, nullable: true)]
    private $duedatewarningUnit;

    #[ORM\Column(name: 'duedatecritical_unit', type: 'string', length: 255, nullable: true)]
    private $duedatecriticalUnit;

    #[ORM\Column(name: 'display_options', type: 'text', length: 65535, nullable: true)]
    private $displayOptions;

    #[ORM\Column(name: 'is_deleted_ldap', type: 'boolean', options: ['default' => 0])]
    private $isDeletedLdap = 0;

    #[ORM\Column(name: 'pdffont', type: 'string', length: 255, nullable: true)]
    private $pdffont;

    #[ORM\Column(name: 'picture', type: 'string', length: 255, nullable: true)]
    private $picture;

    #[ORM\Column(name: 'begin_date', type: 'datetime', nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    private $endDate;

    #[ORM\Column(name: 'keep_devices_when_purging_item', type: 'boolean', nullable: true)]
    private $keepDevicesWhenPurgingItem;

    #[ORM\Column(name: 'privatebookmarkorder', type: 'text', nullable: true)]
    private $privatebookmarkorder;

    #[ORM\Column(name: 'backcreated', type: 'boolean', nullable: true)]
    private $backcreated;

    #[ORM\Column(name: 'task_state', type: 'integer', nullable: true)]
    private $taskState;

    #[ORM\Column(name: 'layout', type: 'string', length: 20, nullable: true)]
    private $layout;

    #[ORM\Column(name: 'palette', type: 'string', length: 20, nullable: true)]
    private $palette;

    #[ORM\Column(name: 'set_default_requester', type: 'boolean', nullable: true)]
    private $setDefaultRequester;

    #[ORM\Column(name: 'lock_autolock_mode', type: 'boolean', nullable: true)]
    private $lockAutolockMode;

    #[ORM\Column(name: 'lock_directunlock_notification', type: 'boolean', nullable: true)]
    private $lockDirectunlockNotification;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\Column(name: 'highcontrast_css', type: 'boolean', nullable: true, options: ['default' => 0])]
    private $highcontrastCss = 0;

    #[ORM\Column(name: 'plannings', type: 'text', length: 65535, nullable: true)]
    private $plannings;

    #[ORM\Column(name: 'sync_field', type: 'string', length: 255, nullable: true)]
    private $syncField;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'users_id_supervisor', type: 'integer', options: ['default' => 0])]
    private $usersIdSupervisor = 0;

    #[ORM\Column(name: 'timezone', type: 'string', length: 50, nullable: true)]
    private $timezone;

    #[ORM\Column(name: 'default_dashboard_central', type: 'string', length: 100, nullable: true)]
    private $defaultDashboardCentral;

    #[ORM\Column(name: 'default_dashboard_assets', type: 'string', length: 100, nullable: true)]
    private $defaultDashboardAssets;

    #[ORM\Column(name: 'default_dashboard_helpdesk', type: 'string', length: 100, nullable: true)]
    private $defaultDashboardHelpdesk;

    #[ORM\Column(name: 'default_dashboard_mini_ticket', type: 'string', length: 100, nullable: true)]
    private $defaultDashboardMiniTicket;

    #[ORM\Column(name: 'access_zoom_level', type: 'smallint', options: ['default' => 100], nullable: true)]
    private $accessZoomLevel = 100;

    #[ORM\Column(name: 'access_font', type: 'string', length: 100, nullable: true)]
    private $accessFont = null;

    #[ORM\Column(name: 'access_shortcuts', type: 'boolean', options: ['default' => 0], nullable: true)]
    private $accessShortcuts = 0;

    #[ORM\Column(name: 'access_custom_shortcuts', type: 'text', nullable: true)]
    private $accessCustomShortcuts;

    #[ORM\Column(name: 'menu_favorite', type: 'text', nullable: true, options: ["default" => "{}"])]
    private $menuFavorite = '{}';

    #[ORM\Column(name: 'menu_favorite_on', type: 'text', length: 65535, nullable: true, options: ["default" => 1])]
    private $menuFavoriteOn = 1;

    #[ORM\Column(name: 'menu_position', type: 'text', length: 65535, nullable: true, options: ["default" => "menu-left"])]
    private $menuPosition = "menu-left";

    #[ORM\Column(name: 'menu_small', type: 'text', length: 65535, nullable: true, options: ["default" => false])]
    private $menuSmall = false;

    #[ORM\Column(name: 'menu_width', type: 'text', length: 65535, nullable: true, options: ["default" => null])]
    private $menuWidth = null;

    #[ORM\Column(name: 'menu_open', type: 'text', nullable: true, options: ["default" => "[]"])]
    private $menuOpen = "[]";

    #[ORM\Column(name: 'bubble_pos', type: 'text', length: 65535, nullable: true)]
    private $bubblePos;

    #[ORM\Column(name: 'accessibility_menu', type: 'boolean', options: ['default' => 0])]
    private $accessibilityMenu = 0;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChangeUser::class)]
    private Collection $changeUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: GroupUser::class)]
    private Collection $groupUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: KnowbaseItemUser::class)]
    private Collection $knowbaseitemUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProblemUser::class)]
    private Collection $problemUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProfileUser::class)]
    private Collection $profileUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ReminderUser::class)]
    private Collection $reminderUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RSSFeedUser::class)]
    private Collection $rssfeedUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SavedSearchUser::class)]
    private Collection $savedsearchUsers;

    public function __construct()
    {
        $this->changeUsers = new ArrayCollection();
        $this->groupUsers = new ArrayCollection();
        $this->knowbaseitemUsers = new ArrayCollection();
        $this->problemUsers = new ArrayCollection();
        $this->profileUsers = new ArrayCollection();
        $this->reminderUsers = new ArrayCollection();
        $this->rssfeedUsers = new ArrayCollection();
        $this->savedsearchUsers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordLastUpdate(): ?\DateTime
    {
        return $this->passwordLastUpdate;
    }

    public function setPasswordLastUpdate(?\DateTime $passwordLastUpdate): self
    {
        $this->passwordLastUpdate = $passwordLastUpdate;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getRealname(): ?string
    {
        return $this->realname;
    }

    public function setRealname(?string $realname): self
    {
        $this->realname = $realname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }


    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getUseMode(): ?int
    {
        return $this->useMode;
    }

    public function setUseMode(?int $useMode): self
    {
        $this->useMode = $useMode;

        return $this;
    }

    public function getListLimit(): ?int
    {
        return $this->listLimit;
    }

    public function setListLimit(?int $listLimit): self
    {
        $this->listLimit = $listLimit;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getAuthsId(): ?int
    {
        return $this->authsId;
    }

    public function setAuthsId(?int $authsId): self
    {
        $this->authsId = $authsId;

        return $this;
    }

    public function getAuthtype(): ?int
    {
        return $this->authtype;
    }

    public function setAuthtype(?int $authtype): self
    {
        $this->authtype = $authtype;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->dateMod;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function setDateMod(): self
    {
        $this->dateMod = new \DateTime();

        return $this;
    }

    public function getDateSync(): ?\DateTime
    {
        return $this->dateSync;
    }

    public function setDateSync(?\DateTime $dateSync): self
    {
        $this->dateSync = $dateSync;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getDateFormat(): ?int
    {
        return $this->dateFormat;
    }

    public function setDateFormat(?int $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getNumberFormat(): ?int
    {
        return $this->numberFormat;
    }

    public function setNumberFormat(?int $numberFormat): self
    {
        $this->numberFormat = $numberFormat;

        return $this;
    }

    public function getNamesFormat(): ?int
    {
        return $this->namesFormat;
    }

    public function setNamesFormat(?int $namesFormat): self
    {
        $this->namesFormat = $namesFormat;

        return $this;
    }

    public function getCsvDelimiter(): ?string
    {
        return $this->csvDelimiter;
    }

    public function setCsvDelimiter(?string $csvDelimiter): self
    {
        $this->csvDelimiter = $csvDelimiter;

        return $this;
    }

    public function getIsIdsVisible(): ?bool
    {
        return $this->isIdsVisible;
    }

    public function setIsIdsVisible(?bool $isIdsVisible): self
    {
        $this->isIdsVisible = $isIdsVisible;

        return $this;
    }

    public function getUseFlatDropdowntree(): ?bool
    {
        return $this->useFlatDropdowntree;
    }

    public function setUseFlatDropdowntree(?bool $useFlatDropdowntree): self
    {
        $this->useFlatDropdowntree = $useFlatDropdowntree;

        return $this;
    }

    public function getShowJobsAtLogin(): ?bool
    {
        return $this->showJobsAtLogin;
    }

    public function setShowJobsAtLogin(?bool $showJobsAtLogin): self
    {
        $this->showJobsAtLogin = $showJobsAtLogin;

        return $this;
    }

    public function getPriority1(): ?string
    {
        return $this->priority1;
    }

    public function setPriority1(?string $priority1): self
    {
        $this->priority1 = $priority1;

        return $this;
    }

    public function getPriority2(): ?string
    {
        return $this->priority2;
    }

    public function setPriority2(?string $priority2): self
    {
        $this->priority2 = $priority2;

        return $this;
    }

    public function getPriority3(): ?string
    {
        return $this->priority3;
    }

    public function setPriority3(?string $priority3): self
    {
        $this->priority3 = $priority3;

        return $this;
    }

    public function getPriority4(): ?string
    {
        return $this->priority4;
    }

    public function setPriority4(?string $priority4): self
    {
        $this->priority4 = $priority4;

        return $this;
    }

    public function getPriority5(): ?string
    {
        return $this->priority5;
    }

    public function setPriority5(?string $priority5): self
    {
        $this->priority5 = $priority5;

        return $this;
    }

    public function getPriority6(): ?string
    {
        return $this->priority6;
    }

    public function setPriority6(?string $priority6): self
    {
        $this->priority6 = $priority6;

        return $this;
    }

    public function getFollowupPrivate(): ?bool
    {
        return $this->followupPrivate;
    }

    public function setFollowupPrivate(?bool $followupPrivate): self
    {
        $this->followupPrivate = $followupPrivate;

        return $this;
    }

    public function getTaskPrivate(): ?bool
    {
        return $this->taskPrivate;
    }

    public function setTaskPrivate(?bool $taskPrivate): self
    {
        $this->taskPrivate = $taskPrivate;

        return $this;
    }



    public function getPasswordForgetToken(): ?string
    {
        return $this->passwordForgetToken;
    }

    public function setPasswordForgetToken(?string $passwordForgetToken): self
    {
        $this->passwordForgetToken = $passwordForgetToken;

        return $this;
    }

    public function getPasswordForgetTokenDate(): ?\DateTime
    {
        return $this->passwordForgetTokenDate;
    }

    public function setPasswordForgetTokenDate(?\DateTime $passwordForgetTokenDate): self
    {
        $this->passwordForgetTokenDate = $passwordForgetTokenDate;

        return $this;
    }

    public function getUserDn(): ?string
    {
        return $this->userDn;
    }

    public function setUserDn(?string $userDn): self
    {
        $this->userDn = $userDn;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getShowCountOnTabs(): ?bool
    {
        return $this->showCountOnTabs;
    }

    public function setShowCountOnTabs(?bool $showCountOnTabs): self
    {
        $this->showCountOnTabs = $showCountOnTabs;

        return $this;
    }

    public function getRefreshViews(): ?int
    {
        return $this->refreshViews;
    }

    public function setRefreshViews(?int $refreshViews): self
    {
        $this->refreshViews = $refreshViews;

        return $this;
    }

    public function getSetDefaultTech(): ?bool
    {
        return $this->setDefaultTech;
    }

    public function setSetDefaultTech(?bool $setDefaultTech): self
    {
        $this->setDefaultTech = $setDefaultTech;

        return $this;
    }

    public function getPersonalToken(): ?string
    {
        return $this->personalToken;
    }

    public function setPersonalToken(?string $personalToken): self
    {
        $this->personalToken = $personalToken;

        return $this;
    }

    public function getPersonalTokenDate(): ?\DateTime
    {
        return $this->personalTokenDate;
    }

    public function setPersonalTokenDate(?\DateTime $personalTokenDate): self
    {
        $this->personalTokenDate = $personalTokenDate;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getApiTokenDate(): ?\DateTime
    {
        return $this->apiTokenDate;
    }

    public function setApiTokenDate(?\DateTime $apiTokenDate): self
    {
        $this->apiTokenDate = $apiTokenDate;

        return $this;
    }

    public function getCookieToken(): ?string
    {
        return $this->cookieToken;
    }

    public function setCookieToken(?string $cookieToken): self
    {
        $this->cookieToken = $cookieToken;

        return $this;
    }

    public function getCookieTokenDate(): ?\DateTime
    {
        return $this->cookieTokenDate;
    }

    public function setCookieTokenDate(?\DateTime $cookieTokenDate): self
    {
        $this->cookieTokenDate = $cookieTokenDate;

        return $this;
    }

    public function getDisplayCountOnHome(): ?int
    {
        return $this->displayCountOnHome;
    }

    public function setDisplayCountOnHome(?int $displayCountOnHome): self
    {
        $this->displayCountOnHome = $displayCountOnHome;

        return $this;
    }

    public function getNotificationToMyself(): ?bool
    {
        return $this->notificationToMyself;
    }

    public function setNotificationToMyself(?bool $notificationToMyself): self
    {
        $this->notificationToMyself = $notificationToMyself;

        return $this;
    }

    public function getDuedateokColor(): ?string
    {
        return $this->duedateokColor;
    }

    public function setDuedateokColor(?string $duedateokColor): self
    {
        $this->duedateokColor = $duedateokColor;

        return $this;
    }

    public function getDuedatewarningColor(): ?string
    {
        return $this->duedatewarningColor;
    }

    public function setDuedatewarningColor(?string $duedatewarningColor): self
    {
        $this->duedatewarningColor = $duedatewarningColor;

        return $this;
    }

    public function getDuedatecriticalColor(): ?string
    {
        return $this->duedatecriticalColor;
    }

    public function setDuedatecriticalColor(?string $duedatecriticalColor): self
    {
        $this->duedatecriticalColor = $duedatecriticalColor;

        return $this;
    }

    public function getDuedatewarningLess(): ?int
    {
        return $this->duedatewarningLess;
    }

    public function setDuedatewarningLess(?int $duedatewarningLess): self
    {
        $this->duedatewarningLess = $duedatewarningLess;

        return $this;
    }

    public function getDuedatecriticalLess(): ?int
    {
        return $this->duedatecriticalLess;
    }

    public function setDuedatecriticalLess(?int $duedatecriticalLess): self
    {
        $this->duedatecriticalLess = $duedatecriticalLess;

        return $this;
    }

    public function getDuedatewarningUnit(): ?string
    {
        return $this->duedatewarningUnit;
    }

    public function setDuedatewarningUnit(?string $duedatewarningUnit): self
    {
        $this->duedatewarningUnit = $duedatewarningUnit;

        return $this;
    }

    public function getDuedatecriticalUnit(): ?string
    {
        return $this->duedatecriticalUnit;
    }

    public function setDuedatecriticalUnit(?string $duedatecriticalUnit): self
    {
        $this->duedatecriticalUnit = $duedatecriticalUnit;

        return $this;
    }

    public function getDisplayOptions(): ?string
    {
        return $this->displayOptions;
    }

    public function setDisplayOptions(?string $displayOptions): self
    {
        $this->displayOptions = $displayOptions;

        return $this;
    }

    public function getIsDeletedLdap(): ?bool
    {
        return $this->isDeletedLdap;
    }

    public function setIsDeletedLdap(?bool $isDeletedLdap): self
    {
        $this->isDeletedLdap = $isDeletedLdap;

        return $this;
    }

    public function getPdfFont(): ?string
    {
        return $this->pdffont;
    }

    public function setPdfFont(?string $pdffont): self
    {
        $this->pdffont = $pdffont;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getBeginDate(): ?\DateTime
    {
        return $this->beginDate;
    }

    public function setBeginDate(\DateTimeInterface|string|null $beginDate): self
    {
        if (is_string($beginDate)) {
            $beginDate = new \DateTime($beginDate);
        }
        $this->beginDate = $beginDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface|string|null $endDate): self
    {
        if (is_string($endDate)) {
            $endDate = new \DateTime($endDate);
        }
        $this->endDate = $endDate;

        return $this;
    }

    public function getKeepDevicesWhenPurgingItem(): ?bool
    {
        return $this->keepDevicesWhenPurgingItem;
    }

    public function setKeepDevicesWhenPurgingItem(?bool $keepDevicesWhenPurgingItem): self
    {
        $this->keepDevicesWhenPurgingItem = $keepDevicesWhenPurgingItem;

        return $this;
    }

    public function getPrivatebookmarkorder(): ?string
    {
        return $this->privatebookmarkorder;
    }

    public function setPrivatebookmarkorder(?string $privatebookmarkorder): self
    {
        $this->privatebookmarkorder = $privatebookmarkorder;

        return $this;
    }

    public function getBackcreated(): ?bool
    {
        return $this->backcreated;
    }

    public function setBackcreated(?bool $backcreated): self
    {
        $this->backcreated = $backcreated;

        return $this;
    }

    public function getTaskState(): ?int
    {
        return $this->taskState;
    }

    public function setTaskState(?int $taskState): self
    {
        $this->taskState = $taskState;

        return $this;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function getPalette(): ?string
    {
        return $this->palette;
    }

    public function setPalette(?string $palette): self
    {
        $this->palette = $palette;

        return $this;
    }

    public function getSetDefaultRequester(): ?bool
    {
        return $this->setDefaultRequester;
    }

    public function setSetDefaultRequester(?bool $setDefaultRequester): self
    {
        $this->setDefaultRequester = $setDefaultRequester;

        return $this;
    }

    public function getLockAutolockMode(): ?bool
    {
        return $this->lockAutolockMode;
    }

    public function setLockAutolockMode(?bool $lockAutolockMode): self
    {
        $this->lockAutolockMode = $lockAutolockMode;

        return $this;
    }

    public function getLockDirectunlockNotification(): ?bool
    {
        return $this->lockDirectunlockNotification;
    }

    public function setLockDirectunlockNotification(?bool $lockDirectunlockNotification): self
    {
        $this->lockDirectunlockNotification = $lockDirectunlockNotification;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new \DateTime();

        return $this;
    }

    public function getHighcontrastCss(): ?bool
    {
        return $this->highcontrastCss;
    }

    public function setHighcontrastCss(?bool $highcontrastCss): self
    {
        $this->highcontrastCss = $highcontrastCss;

        return $this;
    }

    public function getPlannings(): ?string
    {
        return $this->plannings;
    }

    public function setPlannings(?string $plannings): self
    {
        $this->plannings = $plannings;

        return $this;
    }

    public function getSyncField(): ?string
    {
        return $this->syncField;
    }

    public function setSyncField(?string $syncField): self
    {
        $this->syncField = $syncField;

        return $this;
    }

    public function getUsersIdSupervisor(): ?int
    {
        return $this->usersIdSupervisor;
    }

    public function setUsersIdSupervisor(?int $usersIdSupervisor): self
    {
        $this->usersIdSupervisor = $usersIdSupervisor;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getDefaultDashboardCentral(): ?string
    {
        return $this->defaultDashboardCentral;
    }

    public function setDefaultDashboardCentral(?string $defaultDashboardCentral): self
    {
        $this->defaultDashboardCentral = $defaultDashboardCentral;

        return $this;
    }

    public function getDefaultDashboardAssets(): ?string
    {
        return $this->defaultDashboardAssets;
    }

    public function setDefaultDashboardAssets(?string $defaultDashboardAssets): self
    {
        $this->defaultDashboardAssets = $defaultDashboardAssets;

        return $this;
    }

    public function getDefaultDashboardHelpdesk(): ?string
    {
        return $this->defaultDashboardHelpdesk;
    }

    public function setDefaultDashboardHelpdesk(?string $defaultDashboardHelpdesk): self
    {
        $this->defaultDashboardHelpdesk = $defaultDashboardHelpdesk;

        return $this;
    }

    public function getDefaultDashboardMiniTicket(): ?string
    {
        return $this->defaultDashboardMiniTicket;
    }

    public function setDefaultDashboardMiniTicket(?string $defaultDashboardMiniTicket): self
    {
        $this->defaultDashboardMiniTicket = $defaultDashboardMiniTicket;

        return $this;
    }

    public function getAccessZoomLevel(): ?int
    {
        return $this->accessZoomLevel;
    }

    public function setAccessZoomLevel(?int $accessZoomLevel): self
    {
        $this->accessZoomLevel = $accessZoomLevel;

        return $this;
    }

    public function getAccessFont(): ?string
    {
        return $this->accessFont;
    }

    public function setAccessFont(?string $accessFont): self
    {
        $this->accessFont = $accessFont;

        return $this;
    }

    public function getAccessShortcuts(): ?bool
    {
        return $this->accessShortcuts;
    }

    public function setAccessShortcuts(?bool $accessShortcuts): self
    {
        $this->accessShortcuts = $accessShortcuts;

        return $this;
    }

    public function getAccessCustomShortcuts(): ?string
    {
        return $this->accessCustomShortcuts;
    }

    public function setAccessCustomShortcuts(?string $accessCustomShortcuts): self
    {
        $this->accessCustomShortcuts = $accessCustomShortcuts;

        return $this;
    }

    public function getMenuFavorite(): ?string
    {
        return $this->menuFavorite;
    }

    public function setMenuFavorite(?string $menuFavorite): self
    {
        $this->menuFavorite = $menuFavorite;

        return $this;
    }

    public function getMenuFavoriteOn(): ?string
    {
        return $this->menuFavoriteOn;
    }

    public function setMenuFavoriteOn(?string $menuFavoriteOn): self
    {
        $this->menuFavoriteOn = $menuFavoriteOn;

        return $this;
    }

    public function getMenuPosition(): ?string
    {
        return $this->menuPosition;
    }

    public function setMenuPosition(?string $menuPosition): self
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    public function getMenuSmall(): ?string
    {
        return $this->menuSmall;
    }

    public function setMenuSmall(?string $menuSmall): self
    {
        $this->menuSmall = $menuSmall;

        return $this;
    }

    public function getMenuWidth(): ?string
    {
        return $this->menuWidth;
    }

    public function setMenuWidth(?string $menuWidth): self
    {
        $this->menuWidth = $menuWidth;

        return $this;
    }

    public function getMenuOpen(): ?string
    {
        return $this->menuOpen;
    }

    public function setMenuOpen(?string $menuOpen): self
    {
        $this->menuOpen = $menuOpen;

        return $this;
    }

    public function getBubblePos(): ?string
    {
        return $this->bubblePos;
    }

    public function setBubblePos(?string $bubblePos): self
    {
        $this->bubblePos = $bubblePos;

        return $this;
    }

    public function getAccessibilityMenu(): ?bool
    {
        return $this->accessibilityMenu;
    }

    public function setAccessibilityMenu(?bool $accessibilityMenu): self
    {
        $this->accessibilityMenu = $accessibilityMenu;

        return $this;
    }



    /**
     * Get the value of changeUsers
     */
    public function getChangeUsers()
    {
        return $this->changeUsers;
    }

    /**
     * Set the value of changeUsers
     *
     * @return  self
     */
    public function setChangeUsers($changeUsers)
    {
        $this->changeUsers = $changeUsers;

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

    /**
     * Get the value of knowbaseitemUsers
     */
    public function getKnowbaseItemUsers()
    {
        return $this->knowbaseitemUsers;
    }

    /**
     * Set the value of knowbaseitemUsers
     *
     * @return  self
     */
    public function setKnowbaseItemUsers($knowbaseitemUsers)
    {
        $this->knowbaseitemUsers = $knowbaseitemUsers;

        return $this;
    }

    /**
     * Get the value of problemUsers
     */
    public function getProblemUsers()
    {
        return $this->problemUsers;
    }

    /**
     * Set the value of problemUsers
     *
     * @return  self
     */
    public function setProblemUsers($problemUsers)
    {
        $this->problemUsers = $problemUsers;

        return $this;
    }

    /**
     * Get the value of profileUsers
     */
    public function getProfileUsers()
    {
        return $this->profileUsers;
    }

    /**
     * Set the value of profileUsers
     *
     * @return  self
     */
    public function setProfileUsers($profileUsers)
    {
        $this->profileUsers = $profileUsers;

        return $this;
    }

    /**
     * Get the value of reminderUsers
     */
    public function getReminderUsers()
    {
        return $this->reminderUsers;
    }

    /**
     * Set the value of reminderUsers
     *
     * @return  self
     */
    public function setReminderUsers($reminderUsers)
    {
        $this->reminderUsers = $reminderUsers;

        return $this;
    }

    /**
     * Get the value of rssfeedUsers
     */
    public function getRSSFeedUsers()
    {
        return $this->rssfeedUsers;
    }

    /**
     * Set the value of rssfeedUsers
     *
     * @return  self
     */
    public function setRSSFeedUsers($rssfeedUsers)
    {
        $this->rssfeedUsers = $rssfeedUsers;

        return $this;
    }

    /**
     * Get the value of savedsearchUsers
     */
    public function getSavedSearchUsers()
    {
        return $this->savedsearchUsers;
    }

    /**
     * Set the value of savedsearchUsers
     *
     * @return  self
     */
    public function setSavedSearchUsers($savedsearchUsers)
    {
        $this->savedsearchUsers = $savedsearchUsers;

        return $this;
    }

    /**
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     *
     * @return  self
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

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
     * Get the value of usertitle
     */
    public function getUsertitle(): ?Usertitle
    {
        return $this->usertitle;
    }

    /**
     * Set the value of usertitle
     *
     * @param ?Usertitle $usertitle
     * @return self
     */
    public function setUsertitle(?Usertitle $usertitle): self
    {
        $this->usertitle = $usertitle;

        return $this;
    }

    /**
     * Get the value of usercategory
     */
    public function getUsercategory()
    {
        return $this->usercategory;
    }

    /**
     * Set the value of usercategory
     *
     * @return  self
     */
    public function setUsercategory($usercategory)
    {
        $this->usercategory = $usercategory;

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
     * Get the value of defaultRequesttype
     */
    public function getDefaultRequesttype(): ?EntitiesRequestType
    {
        return $this->defaultRequesttype;
    }

    /**
     * Set the value of defaultRequesttype
     *
     * @return  self
     */
    public function setDefaultRequesttype(?EntitiesRequestType $defaultRequesttype): self
    {
        $this->defaultRequesttype = $defaultRequesttype;

        return $this;
    }
}
