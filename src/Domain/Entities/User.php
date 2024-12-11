<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $password;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $password_last_update;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $phone2;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mobile;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $realname;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $firstname;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ['comment' => 'see define.php CFG_GLPI[language] array'])]
    private $language;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $use_mode;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $list_limit;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_active;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $auths_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $authtype;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $last_login;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_sync;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $profiles_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $usertitles_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $usercategories_id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $date_format;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $number_format;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $names_format;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private $csv_delimiter;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $is_ids_visible;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $use_flat_dropdowntree;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $show_jobs_at_login;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_1;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_2;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_3;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_4;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_5;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $priority_6;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $followup_private;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $task_private;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $default_requesttypes_id;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private $password_forget_token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $password_forget_token_date;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $user_dn;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $registration_number;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $show_count_on_tabs;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $refresh_views;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $set_default_tech;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $personal_token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $personal_token_date;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $api_token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $api_token_date;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $cookie_token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $cookie_token_date;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $display_count_on_home;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $notification_to_myself;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $duedateok_color;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $duedatewarning_color;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $duedatecritical_color;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $duedatewarning_less;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $duedatecritical_less;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $duedatewarning_unit;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $duedatecritical_unit;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $display_options;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted_ldap;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $pdffont;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $picture;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $keep_devices_when_purging_item;

    #[ORM\Column(type: 'text', nullable: true)]
    private $privatebookmarkorder;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $backcreated;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $task_state;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $layout;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $palette;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $set_default_requester;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $lock_autolock_mode;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $lock_directunlock_notification;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => 0])]
    private $highcontrast_css;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $plannings;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $sync_field;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_supervisor;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $timezone;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $default_dashboard_central;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $default_dashboard_assets;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $default_dashboard_helpdesk;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $default_dashboard_mini_ticket;

    #[ORM\Column(type: 'smallint', options: ['default' => 100], nullable: true)]
    private $access_zoom_level;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $access_font;

    #[ORM\Column(type: 'boolean', options: ['default' => 0], nullable: true)]
    private $access_shortcuts;

    #[ORM\Column(type: 'text', nullable: true)]
    private $access_custom_shortcuts;

    #[ORM\Column(type: 'text', nullable: true, options: ["default" => "{}"])]
    private $menu_favorite;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ["default" => "1"])]
    private $menu_favorite_on;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ["default" => "menu-left"])]
    private $menu_position;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ["default" => "false"])]
    private $menu_small;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ["default" => "null"])]
    private $menu_width;

    #[ORM\Column(type: 'text', nullable: true, options: ["default" => "[]"])]
    private $menu_open;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $bubble_pos;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $accessibility_menu;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChangeUser::class)]
    private Collection $changeUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: GroupUser::class)]
    private Collection $groupUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: KnowbaseitemUser::class)]
    private Collection $knowbaseitemUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProblemUser::class)]
    private Collection $problemUsers;


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
        return $this->password_last_update;
    }

    public function setPasswordLastUpdate(?\DateTime $password_last_update): self
    {
        $this->password_last_update = $password_last_update;

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

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(?int $locations_id): self
    {
        $this->locations_id = $locations_id;

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
        return $this->use_mode;
    }

    public function setUseMode(?int $use_mode): self
    {
        $this->use_mode = $use_mode;

        return $this;
    }

    public function getListLimit(): ?int
    {
        return $this->list_limit;
    }

    public function setListLimit(?int $list_limit): self
    {
        $this->list_limit = $list_limit;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): self
    {
        $this->is_active = $is_active;

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
        return $this->auths_id;
    }

    public function setAuthsId(?int $auths_id): self
    {
        $this->auths_id = $auths_id;

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
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateSync(): ?\DateTime
    {
        return $this->date_sync;
    }

    public function setDateSync(?\DateTime $date_sync): self
    {
        $this->date_sync = $date_sync;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getProfilesId(): ?int
    {
        return $this->profiles_id;
    }

    public function setProfilesId(?int $profiles_id): self
    {
        $this->profiles_id = $profiles_id;

        return $this;
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

    public function getUsertitlesId(): ?int
    {
        return $this->usertitles_id;
    }

    public function setUsertitlesId(?int $usertitles_id): self
    {
        $this->usertitles_id = $usertitles_id;

        return $this;
    }

    public function getUsercategoriesId(): ?int
    {
        return $this->usercategories_id;
    }

    public function setUsercategoriesId(?int $usercategories_id): self
    {
        $this->usercategories_id = $usercategories_id;

        return $this;
    }

    public function getDateFormat(): ?int
    {
        return $this->date_format;
    }

    public function setDateFormat(?int $date_format): self
    {
        $this->date_format = $date_format;

        return $this;
    }

    public function getNumberFormat(): ?int
    {
        return $this->number_format;
    }

    public function setNumberFormat(?int $number_format): self
    {
        $this->number_format = $number_format;

        return $this;
    }

    public function getNamesFormat(): ?int
    {
        return $this->names_format;
    }

    public function setNamesFormat(?int $names_format): self
    {
        $this->names_format = $names_format;

        return $this;
    }

    public function getCsvDelimiter(): ?string
    {
        return $this->csv_delimiter;
    }

    public function setCsvDelimiter(?string $csv_delimiter): self
    {
        $this->csv_delimiter = $csv_delimiter;

        return $this;
    }

    public function getIsIdsVisible(): ?bool
    {
        return $this->is_ids_visible;
    }

    public function setIsIdsVisible(?bool $is_ids_visible): self
    {
        $this->is_ids_visible = $is_ids_visible;

        return $this;
    }

    public function getUseFlatDropdowntree(): ?bool
    {
        return $this->use_flat_dropdowntree;
    }

    public function setUseFlatDropdowntree(?bool $use_flat_dropdowntree): self
    {
        $this->use_flat_dropdowntree = $use_flat_dropdowntree;

        return $this;
    }

    public function getShowJobsAtLogin(): ?bool
    {
        return $this->show_jobs_at_login;
    }

    public function setShowJobsAtLogin(?bool $show_jobs_at_login): self
    {
        $this->show_jobs_at_login = $show_jobs_at_login;

        return $this;
    }

    public function getPriority1(): ?string
    {
        return $this->priority_1;
    }

    public function setPriority1(?string $priority_1): self
    {
        $this->priority_1 = $priority_1;

        return $this;
    }

    public function getPriority2(): ?string
    {
        return $this->priority_2;
    }

    public function setPriority2(?string $priority_2): self
    {
        $this->priority_2 = $priority_2;

        return $this;
    }

    public function getPriority3(): ?string
    {
        return $this->priority_3;
    }

    public function setPriority3(?string $priority_3): self
    {
        $this->priority_3 = $priority_3;

        return $this;
    }

    public function getPriority4(): ?string
    {
        return $this->priority_4;
    }

    public function setPriority4(?string $priority_4): self
    {
        $this->priority_4 = $priority_4;

        return $this;
    }

    public function getPriority5(): ?string
    {
        return $this->priority_5;
    }

    public function setPriority5(?string $priority_5): self
    {
        $this->priority_5 = $priority_5;

        return $this;
    }

    public function getPriority6(): ?string
    {
        return $this->priority_6;
    }

    public function setPriority6(?string $priority_6): self
    {
        $this->priority_6 = $priority_6;

        return $this;
    }

    public function getFollowupPrivate(): ?bool
    {
        return $this->followup_private;
    }

    public function setFollowupPrivate(?bool $followup_private): self
    {
        $this->followup_private = $followup_private;

        return $this;
    }

    public function getTaskPrivate(): ?bool
    {
        return $this->task_private;
    }

    public function setTaskPrivate(?bool $task_private): self
    {
        $this->task_private = $task_private;

        return $this;
    }

    public function getDefaultRequesttypesId(): ?int
    {
        return $this->default_requesttypes_id;
    }

    public function setDefaultRequesttypesId(?int $default_requesttypes_id): self
    {
        $this->default_requesttypes_id = $default_requesttypes_id;

        return $this;
    }

    public function getPasswordForgetToken(): ?string
    {
        return $this->password_forget_token;
    }

    public function setPasswordForgetToken(?string $password_forget_token): self
    {
        $this->password_forget_token = $password_forget_token;

        return $this;
    }

    public function getPasswordForgetTokenDate(): ?\DateTime
    {
        return $this->password_forget_token_date;
    }

    public function setPasswordForgetTokenDate(?\DateTime $password_forget_token_date): self
    {
        $this->password_forget_token_date = $password_forget_token_date;

        return $this;
    }

    public function getUserDn(): ?string
    {
        return $this->user_dn;
    }

    public function setUserDn(?string $user_dn): self
    {
        $this->user_dn = $user_dn;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registration_number;
    }

    public function setRegistrationNumber(?string $registration_number): self
    {
        $this->registration_number = $registration_number;

        return $this;
    }

    public function getShowCountOnTabs(): ?bool
    {
        return $this->show_count_on_tabs;
    }

    public function setShowCountOnTabs(?bool $show_count_on_tabs): self
    {
        $this->show_count_on_tabs = $show_count_on_tabs;

        return $this;
    }

    public function getRefreshViews(): ?int
    {
        return $this->refresh_views;
    }

    public function setRefreshViews(?int $refresh_views): self
    {
        $this->refresh_views = $refresh_views;

        return $this;
    }

    public function getSetDefaultTech(): ?bool
    {
        return $this->set_default_tech;
    }

    public function setSetDefaultTech(?bool $set_default_tech): self
    {
        $this->set_default_tech = $set_default_tech;

        return $this;
    }

    public function getPersonalToken(): ?string
    {
        return $this->personal_token;
    }

    public function setPersonalToken(?string $personal_token): self
    {
        $this->personal_token = $personal_token;

        return $this;
    }

    public function getPersonalTokenDate(): ?\DateTime
    {
        return $this->personal_token_date;
    }

    public function setPersonalTokenDate(?\DateTime $personal_token_date): self
    {
        $this->personal_token_date = $personal_token_date;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->api_token;
    }

    public function setApiToken(?string $api_token): self
    {
        $this->api_token = $api_token;

        return $this;
    }

    public function getApiTokenDate(): ?\DateTime
    {
        return $this->api_token_date;
    }

    public function setApiTokenDate(?\DateTime $api_token_date): self
    {
        $this->api_token_date = $api_token_date;

        return $this;
    }

    public function getCookieToken(): ?string
    {
        return $this->cookie_token;
    }

    public function setCookieToken(?string $cookie_token): self
    {
        $this->cookie_token = $cookie_token;

        return $this;
    }

    public function getCookieTokenDate(): ?\DateTime
    {
        return $this->cookie_token_date;
    }

    public function setCookieTokenDate(?\DateTime $cookie_token_date): self
    {
        $this->cookie_token_date = $cookie_token_date;

        return $this;
    }

    public function getDisplayCountOnHome(): ?int
    {
        return $this->display_count_on_home;
    }

    public function setDisplayCountOnHome(?int $display_count_on_home): self
    {
        $this->display_count_on_home = $display_count_on_home;

        return $this;
    }

    public function getNotificationToMyself(): ?bool
    {
        return $this->notification_to_myself;
    }

    public function setNotificationToMyself(?bool $notification_to_myself): self
    {
        $this->notification_to_myself = $notification_to_myself;

        return $this;
    }

    public function getDuedateokColor(): ?string
    {
        return $this->duedateok_color;
    }

    public function setDuedateokColor(?string $duedateok_color): self
    {
        $this->duedateok_color = $duedateok_color;

        return $this;
    }

    public function getDuedatewarningColor(): ?string
    {
        return $this->duedatewarning_color;
    }

    public function setDuedatewarningColor(?string $duedatewarning_color): self
    {
        $this->duedatewarning_color = $duedatewarning_color;

        return $this;
    }

    public function getDuedatecriticalColor(): ?string
    {
        return $this->duedatecritical_color;
    }

    public function setDuedatecriticalColor(?string $duedatecritical_color): self
    {
        $this->duedatecritical_color = $duedatecritical_color;

        return $this;
    }

    public function getDuedatewarningLess(): ?int
    {
        return $this->duedatewarning_less;
    }

    public function setDuedatewarningLess(?int $duedatewarning_less): self
    {
        $this->duedatewarning_less = $duedatewarning_less;

        return $this;
    }

    public function getDuedatecriticalLess(): ?int
    {
        return $this->duedatecritical_less;
    }

    public function setDuedatecriticalLess(?int $duedatecritical_less): self
    {
        $this->duedatecritical_less = $duedatecritical_less;

        return $this;
    }

    public function getDuedatewarningUnit(): ?string
    {
        return $this->duedatewarning_unit;
    }

    public function setDuedatewarningUnit(?string $duedatewarning_unit): self
    {
        $this->duedatewarning_unit = $duedatewarning_unit;

        return $this;
    }

    public function getDuedatecriticalUnit(): ?string
    {
        return $this->duedatecritical_unit;
    }

    public function setDuedatecriticalUnit(?string $duedatecritical_unit): self
    {
        $this->duedatecritical_unit = $duedatecritical_unit;

        return $this;
    }

    public function getDisplayOptions(): ?string
    {
        return $this->display_options;
    }

    public function setDisplayOptions(?string $display_options): self
    {
        $this->display_options = $display_options;

        return $this;
    }

    public function getIsDeletedLdap(): ?bool
    {
        return $this->is_deleted_ldap;
    }

    public function setIsDeletedLdap(?bool $is_deleted_ldap): self
    {
        $this->is_deleted_ldap = $is_deleted_ldap;

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
        return $this->begin_date;
    }

    public function setBeginDate(?\DateTime $begin_date): self
    {
        $this->begin_date = $begin_date;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTime $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getKeepDevicesWhenPurgingItem(): ?bool
    {
        return $this->keep_devices_when_purging_item;
    }

    public function setKeepDevicesWhenPurgingItem(?bool $keep_devices_when_purging_item): self
    {
        $this->keep_devices_when_purging_item = $keep_devices_when_purging_item;

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
        return $this->task_state;
    }

    public function setTaskState(?int $task_state): self
    {
        $this->task_state = $task_state;

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
        return $this->set_default_requester;
    }

    public function setSetDefaultRequester(?bool $set_default_requester): self
    {
        $this->set_default_requester = $set_default_requester;

        return $this;
    }

    public function getLockAutolockMode(): ?bool
    {
        return $this->lock_autolock_mode;
    }

    public function setLockAutolockMode(?bool $lock_autolock_mode): self
    {
        $this->lock_autolock_mode = $lock_autolock_mode;

        return $this;
    }

    public function getLockDirectunlockNotification(): ?bool
    {
        return $this->lock_directunlock_notification;
    }

    public function setLockDirectunlockNotification(?bool $lock_directunlock_notification): self
    {
        $this->lock_directunlock_notification = $lock_directunlock_notification;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getHighcontrastCss(): ?bool
    {
        return $this->highcontrast_css;
    }

    public function setHighcontrastCss(?bool $highcontrast_css): self
    {
        $this->highcontrast_css = $highcontrast_css;

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
        return $this->sync_field;
    }

    public function setSyncField(?string $sync_field): self
    {
        $this->sync_field = $sync_field;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(?int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getUsersIdSupervisor(): ?int
    {
        return $this->users_id_supervisor;
    }

    public function setUsersIdSupervisor(?int $users_id_supervisor): self
    {
        $this->users_id_supervisor = $users_id_supervisor;

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
        return $this->default_dashboard_central;
    }

    public function setDefaultDashboardCentral(?string $default_dashboard_central): self
    {
        $this->default_dashboard_central = $default_dashboard_central;

        return $this;
    }

    public function getDefaultDashboardAssets(): ?string
    {
        return $this->default_dashboard_assets;
    }

    public function setDefaultDashboardAssets(?string $default_dashboard_assets): self
    {
        $this->default_dashboard_assets = $default_dashboard_assets;

        return $this;
    }

    public function getDefaultDashboardHelpdesk(): ?string
    {
        return $this->default_dashboard_helpdesk;
    }

    public function setDefaultDashboardHelpdesk(?string $default_dashboard_helpdesk): self
    {
        $this->default_dashboard_helpdesk = $default_dashboard_helpdesk;

        return $this;
    }

    public function getDefaultDashboardMiniTicket(): ?string
    {
        return $this->default_dashboard_mini_ticket;
    }

    public function setDefaultDashboardMiniTicket(?string $default_dashboard_mini_ticket): self
    {
        $this->default_dashboard_mini_ticket = $default_dashboard_mini_ticket;

        return $this;
    }

    public function getAccessZoomLevel(): ?int
    {
        return $this->access_zoom_level;
    }

    public function setAccessZoomLevel(?int $access_zoom_level): self
    {
        $this->access_zoom_level = $access_zoom_level;

        return $this;
    }

    public function getAccessFont(): ?string
    {
        return $this->access_font;
    }

    public function setAccessFont(?string $access_font): self
    {
        $this->access_font = $access_font;

        return $this;
    }

    public function getAccessShortcuts(): ?bool
    {
        return $this->access_shortcuts;
    }

    public function setAccessShortcuts(?bool $access_shortcuts): self
    {
        $this->access_shortcuts = $access_shortcuts;

        return $this;
    }

    public function getAccessCustomShortcuts(): ?string
    {
        return $this->access_custom_shortcuts;
    }

    public function setAccessCustomShortcuts(?string $access_custom_shortcuts): self
    {
        $this->access_custom_shortcuts = $access_custom_shortcuts;

        return $this;
    }

    public function getMenuFavorite(): ?string
    {
        return $this->menu_favorite;
    }

    public function setMenuFavorite(?string $menu_favorite): self
    {
        $this->menu_favorite = $menu_favorite;

        return $this;
    }

    public function getMenuFavoriteOn(): ?string
    {
        return $this->menu_favorite_on;
    }

    public function setMenuFavoriteOn(?string $menu_favorite_on): self
    {
        $this->menu_favorite_on = $menu_favorite_on;

        return $this;
    }

    public function getMenuPosition(): ?string
    {
        return $this->menu_position;
    }

    public function setMenuPosition(?string $menu_position): self
    {
        $this->menu_position = $menu_position;

        return $this;
    }

    public function getMenuSmall(): ?string
    {
        return $this->menu_small;
    }

    public function setMenuSmall(?string $menu_small): self
    {
        $this->menu_small = $menu_small;

        return $this;
    }

    public function getMenuWidth(): ?string
    {
        return $this->menu_width;
    }

    public function setMenuWidth(?string $menu_width): self
    {
        $this->menu_width = $menu_width;

        return $this;
    }

    public function getMenuOpen(): ?string
    {
        return $this->menu_open;
    }

    public function setMenuOpen(?string $menu_open): self
    {
        $this->menu_open = $menu_open;

        return $this;
    }

    public function getBubblePos(): ?string
    {
        return $this->bubble_pos;
    }

    public function setBubblePos(?string $bubble_pos): self
    {
        $this->bubble_pos = $bubble_pos;

        return $this;
    }

    public function getAccessibilityMenu(): ?bool
    {
        return $this->accessibility_menu;
    }

    public function setAccessibilityMenu(?bool $accessibility_menu): self
    {
        $this->accessibility_menu = $accessibility_menu;

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
    public function getKnowbaseitemUsers()
    {
        return $this->knowbaseitemUsers;
    }

    /**
     * Set the value of knowbaseitemUsers
     *
     * @return  self
     */
    public function setKnowbaseitemUsers($knowbaseitemUsers)
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
}
