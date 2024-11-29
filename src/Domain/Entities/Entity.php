<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_entities")]
#[ORM\UniqueConstraint(name: "unicity", columns: ['entities_id', 'name'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
#[ORM\Index(name: "tickettemplates_id", columns: ['tickettemplates_id'])]
#[ORM\Index(name: "changetemplates_id", columns: ['changetemplates_id'])]
#[ORM\Index(name: "problemtemplates_id", columns: ['problemtemplates_id'])]
class Entity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(type: 'text', nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $address;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $postcode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $town;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $website;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $phonenumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $fax;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $admin_email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $admin_email_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $admin_reply;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $admin_reply_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $notification_subject_tag;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ldap_dn;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $tag;

    #[ORM\Column(type: 'integer', name: 'authldaps_id', options: ['default' => 0])]
    private $authldaps_id;

    #[ORM\ManyToOne(targetEntity: AuthLdap::class)]
    #[ORM\JoinColumn(name: 'authldaps_id', referencedColumnName: 'id', nullable: false)]
    private ?AuthLdap $authldap;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mail_domain;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $entity_ldapfilter;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $mailing_signature;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $cartridges_alert_repeat;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $consumables_alert_repeat;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_licenses_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_licenses_alert_before_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_certificates_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_certificates_alert_before_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_contracts_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_contracts_alert_before_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_infocoms_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_infocoms_alert_before_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_reservations_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $use_domains_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_domains_alert_close_expiries_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $send_domains_alert_expired_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $autoclose_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -10])]
    private $autopurge_delay;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $notclosed_delay;

    #[ORM\Column(type: 'integer', name: 'calendars_id', options: ['default' => -2])]
    private $calendars_id;

    #[ORM\ManyToOne(targetEntity: Calendar::class)]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: false)]
    private ?Calendar $calendar;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $auto_assign_mode;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $tickettype;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $max_closedate;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $inquest_config;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $inquest_rate;

    #[ORM\Column(type: 'integer', options: ['default' => -10])]
    private $inquest_delay;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $inquest_URL;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_warranty_date;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_use_date;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_buy_date;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_delivery_date;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_order_date;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $tickettemplates_id;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?TicketTemplate $tickettemplate;

    #[ORM\Column(type: 'integer', name: 'changetemplates_id', options: ['default' => -2])]
    private $changetemplates_id;
    
    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?ChangeTemplate $changetemplate;

    #[ORM\Column(type: 'integer', name: 'problemtemplates_id', options: ['default' => -2])]
    private $problemtemplates_id;

    #[ORM\ManyToOne(targetEntity: Problemtemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: false)]
    private ?Problemtemplate $problemtemplate;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $entities_id_software;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $default_contract_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $default_infocom_alert;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $default_cartridges_alarm_threshold;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $default_consumables_alarm_threshold;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $delay_send_emails;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $is_notif_enable_default;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $inquest_duration;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofill_decommission_date;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $suppliers_as_private;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $anonymize_support_agents;

    #[ORM\Column(type: 'integer', options: ['default' => -2])]
    private $enable_custom_css;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $custom_css_code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $latitude;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $longitude;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $altitude;

    #[ORM\OneToMany(mappedBy: 'entity', targetEntity: EntityKnowbaseitem::class)]
    private Collection $entityKnowbaseitems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entitiesId): self
    {
        $this->entities_id = $entitiesId;

        return $this;
    }

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(string $completename): self
    {
        $this->completename = $completename;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sons_cache;
    }

    public function setSonsCache(string $sonsCache): self
    {
        $this->sons_cache = $sonsCache;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestors_cache;
    }

    public function setAncestorsCache(string $ancestorsCache): self
    {
        $this->ancestors_cache = $ancestorsCache;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(string $phonenumber): self
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAdminEmail(): ?string
    {
        return $this->admin_email;
    }

    public function setAdminEmail(string $adminEmail): self
    {
        $this->admin_email = $adminEmail;

        return $this;
    }

    public function getAdminEmailName(): ?string
    {
        return $this->admin_email_name;
    }

    public function setAdminEmailName(string $adminEmailName): self
    {
        $this->admin_email_name = $adminEmailName;

        return $this;
    }

    public function getAdminReply(): ?string
    {
        return $this->admin_reply;
    }

    public function setAdminReply(string $adminReply): self
    {
        $this->admin_reply = $adminReply;

        return $this;
    }

    public function getAdminReplyName(): ?string
    {
        return $this->admin_reply_name;
    }

    public function setAdminReplyName(string $adminReplyName): self
    {
        $this->admin_reply_name = $adminReplyName;

        return $this;
    }

    public function getNotificationSubjectTag(): ?string
    {
        return $this->notification_subject_tag;
    }

    public function setNotificationSubjectTag(string $notificationSubjectTag): self
    {
        $this->notification_subject_tag = $notificationSubjectTag;

        return $this;
    }

    public function getLdapDn(): ?string
    {
        return $this->ldap_dn;
    }

    public function setLdapDn(string $ldapDn): self
    {
        $this->ldap_dn = $ldapDn;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getAuthldapsId(): ?int
    {
        return $this->authldaps_id;
    }

    public function setAuthldapsId(int $authldapId): self
    {
        $this->authldaps_id = $authldapId;

        return $this;
    }

    public function getMailDomain(): ?string
    {
        return $this->mail_domain;
    }

    public function setMailDomain(string $mailDomain): self
    {
        $this->mail_domain = $mailDomain;

        return $this;
    }

    public function getEntityLdapfilter(): ?string
    {
        return $this->entity_ldapfilter;
    }

    public function setEntityLdapfilter(string $entityLdapfilter): self
    {
        $this->entity_ldapfilter = $entityLdapfilter;

        return $this;
    }

    public function getMailingSignature(): ?string
    {
        return $this->mailing_signature;
    }

    public function setMailingSignature(string $mailingSignature): self
    {
        $this->mailing_signature = $mailingSignature;

        return $this;
    }

    public function getCartridgesAlertRepeat(): ?int
    {
        return $this->cartridges_alert_repeat;
    }

    public function setCartridgesAlertRepeat(int $cartridgesAlertRepeat): self
    {
        $this->cartridges_alert_repeat = $cartridgesAlertRepeat;

        return $this;
    }

    public function getConsumablesAlertRepeat(): ?int
    {
        return $this->consumables_alert_repeat;
    }

    public function setConsumablesAlertRepeat(int $consumablesAlertRepeat): self
    {
        $this->consumables_alert_repeat = $consumablesAlertRepeat;

        return $this;
    }

    public function getUseLicensesAlert(): ?int
    {
        return $this->use_licenses_alert;
    }

    public function setUseLicensesAlert(int $useLicensesAlert): self
    {
        $this->use_licenses_alert = $useLicensesAlert;

        return $this;
    }

    public function getSendLicensesAlertBeforeDelay(): ?int
    {
        return $this->send_licenses_alert_before_delay;
    }

    public function setSendLicensesAlertBeforeDelay(int $sendLicensesAlertBeforeDelay): self
    {
        $this->send_licenses_alert_before_delay = $sendLicensesAlertBeforeDelay;

        return $this;
    }

    public function getUseCertificatesAlert(): ?int
    {
        return $this->use_certificates_alert;
    }

    public function setUseCertificatesAlert(int $useCertificatesAlert): self
    {
        $this->use_certificates_alert = $useCertificatesAlert;

        return $this;
    }

    public function getSendCertificatesAlertBeforeDelay(): ?int
    {
        return $this->send_certificates_alert_before_delay;
    }

    public function setSendCertificatesAlertBeforeDelay(int $sendCertificatesAlertBeforeDelay): self
    {
        $this->send_certificates_alert_before_delay = $sendCertificatesAlertBeforeDelay;

        return $this;
    }

    public function getUseContractsAlert(): ?int
    {
        return $this->use_contracts_alert;
    }

    public function setUseContractsAlert(int $useContractsAlert): self
    {
        $this->use_contracts_alert = $useContractsAlert;

        return $this;
    }

    public function getSendContractsAlertBeforeDelay(): ?int
    {
        return $this->send_contracts_alert_before_delay;
    }

    public function setSendContractsAlertBeforeDelay(int $sendContractsAlertBeforeDelay): self
    {
        $this->send_contracts_alert_before_delay = $sendContractsAlertBeforeDelay;

        return $this;
    }

    public function getUseInfocomsAlert(): ?int
    {
        return $this->use_infocoms_alert;
    }

    public function setUseInfocomsAlert(int $useInfocomsAlert): self
    {
        $this->use_infocoms_alert = $useInfocomsAlert;

        return $this;
    }

    public function getSendInfocomsAlertBeforeDelay(): ?int
    {
        return $this->send_infocoms_alert_before_delay;
    }

    public function setSendInfocomsAlertBeforeDelay(int $sendInfocomsAlertBeforeDelay): self
    {
        $this->send_infocoms_alert_before_delay = $sendInfocomsAlertBeforeDelay;

        return $this;
    }

    public function getUseReservationsAlert(): ?int
    {
        return $this->use_reservations_alert;
    }

    public function setUseReservationsAlert(int $useReservationsAlert): self
    {
        $this->use_reservations_alert = $useReservationsAlert;

        return $this;
    }

    public function getUseDomainAlert(): ?int
    {
        return $this->use_domains_alert;
    }

    public function setUseDomainAlert(int $useDomainAlert): self
    {
        $this->use_domains_alert = $useDomainAlert;

        return $this;
    }

    public function getSendDomainAlertCloseExpiriesDelay(): ?int
    {
        return $this->send_domains_alert_close_expiries_delay;
    }

    public function setSendDomainAlertCloseExpiriesDelay(int $sendDomainAlertCloseExpiriesDelay): self
    {
        $this->send_domains_alert_close_expiries_delay = $sendDomainAlertCloseExpiriesDelay;

        return $this;
    }

    public function getSendDomainAlertExpiredDelay(): ?int
    {
        return $this->send_domains_alert_expired_delay;
    }

    public function setSendDomainAlertExpiredDelay(int $sendDomainAlertExpiredDelay): self
    {
        $this->send_domains_alert_expired_delay = $sendDomainAlertExpiredDelay;

        return $this;
    }

    public function getAutocloseDelay(): ?int
    {
        return $this->autoclose_delay;
    }

    public function setAutocloseDelay(int $autocloseDelay): self
    {
        $this->autoclose_delay = $autocloseDelay;

        return $this;
    }

    public function getAutopurgeDelay(): ?int
    {
        return $this->autopurge_delay;
    }

    public function setAutopurgeDelay(int $autopurgeDelay): self
    {
        $this->autopurge_delay = $autopurgeDelay;

        return $this;
    }

    public function getNotclosedDelay(): ?int
    {
        return $this->notclosed_delay;
    }

    public function setNotclosedDelay(int $notclosedDelay): self
    {
        $this->notclosed_delay = $notclosedDelay;

        return $this;
    }

    public function getCalendarsId(): ?int
    {
        return $this->calendars_id;
    }

    public function setCalendarsId(int $calendarsId): self
    {
        $this->calendars_id = $calendarsId;

        return $this;
    }

    public function getAutoAssignMode(): ?int
    {
        return $this->auto_assign_mode;
    }

    public function setAutoAssignMode(int $autoAssignMode): self
    {
        $this->auto_assign_mode = $autoAssignMode;

        return $this;
    }

    public function getTickettype(): ?int
    {
        return $this->tickettype;
    }

    public function setTickettype(int $tickettype): self
    {
        $this->tickettype = $tickettype;

        return $this;
    }

    public function getMaxClosedate(): ?\DateTimeInterface
    {
        return $this->max_closedate;
    }

    public function setMaxClosedate(\DateTimeInterface $maxClosedate): self
    {
        $this->max_closedate = $maxClosedate;

        return $this;
    }

    public function getInquestConfig(): ?int
    {
        return $this->inquest_config;
    }

    public function setInquestConfig(int $inquestConfig): self
    {
        $this->inquest_config = $inquestConfig;

        return $this;
    }

    public function getInquestRate(): ?int
    {
        return $this->inquest_rate;
    }

    public function setInquestRate(int $inquestRate): self
    {
        $this->inquest_rate = $inquestRate;

        return $this;
    }

    public function getInquestDelay(): ?int
    {
        return $this->inquest_delay;
    }

    public function setInquestDelay(int $inquestDelay): self
    {
        $this->inquest_delay = $inquestDelay;

        return $this;
    }

    public function getInquestURL(): ?string
    {
        return $this->inquest_URL;
    }

    public function setInquestURL(string $inquestURL): self
    {
        $this->inquest_URL = $inquestURL;

        return $this;
    }

    public function getAutofillWarrantyDate(): ?string
    {
        return $this->autofill_warranty_date;
    }

    public function setAutofillWarrantyDate(string $autofillWarrantyDate): self
    {
        $this->autofill_warranty_date = $autofillWarrantyDate;

        return $this;
    }

    public function getAutofillUseDate(): ?string
    {
        return $this->autofill_use_date;
    }

    public function setAutofillUseDate(string $autofillUseDate): self
    {
        $this->autofill_use_date = $autofillUseDate;

        return $this;
    }

    public function getAutofillBuyDate(): ?string
    {
        return $this->autofill_buy_date;
    }

    public function setAutofillBuyDate(string $autofillBuyDate): self
    {
        $this->autofill_buy_date = $autofillBuyDate;

        return $this;
    }

    public function getAutofillDeliveryDate(): ?string
    {
        return $this->autofill_delivery_date;
    }

    public function setAutofillDeliveryDate(string $autofillDeliveryDate): self
    {
        $this->autofill_delivery_date = $autofillDeliveryDate;

        return $this;
    }

    public function getAutofillOrderDate(): ?string
    {
        return $this->autofill_order_date;
    }

    public function setAutofillOrderDate(string $autofillOrderDate): self
    {
        $this->autofill_order_date = $autofillOrderDate;

        return $this;
    }

    public function getTicketTemplatesId(): ?int
    {
        return $this->tickettemplates_id;
    }

    public function setTicketTemplatesId(int $ticketTemplatesId): self
    {
        $this->tickettemplates_id = $ticketTemplatesId;

        return $this;
    }

    public function getChangeTemplatesId(): ?int
    {
        return $this->changetemplates_id;
    }

    public function setChangeTemplatesId(int $changeTemplatesId): self
    {
        $this->changetemplates_id = $changeTemplatesId;

        return $this;
    }

    public function getProblemTemplatesId(): ?int
    {
        return $this->problemtemplates_id;
    }

    public function setProblemTemplatesId(int $problemTemplatesId): self
    {
        $this->problemtemplates_id = $problemTemplatesId;

        return $this;
    }

    public function getEntitiesIdSoftware(): ?int
    {
        return $this->entities_id_software;
    }

    public function setEntitiesIdSoftware(int $entitiesIdSoftware): self
    {
        $this->entities_id_software = $entitiesIdSoftware;

        return $this;
    }

    public function getDefaultContractAlert(): ?int
    {
        return $this->default_contract_alert;
    }

    public function setDefaultContractAlert(int $defaultContractAlert): self
    {
        $this->default_contract_alert = $defaultContractAlert;

        return $this;
    }

    public function getDefaultInfocomAlert(): ?int
    {
        return $this->default_infocom_alert;
    }

    public function setDefaultInfocomAlert(int $defaultInfocomAlert): self
    {
        $this->default_infocom_alert = $defaultInfocomAlert;

        return $this;
    }

    public function getDefaultCartridgesAlarmThreshold(): ?int
    {
        return $this->default_cartridges_alarm_threshold;
    }

    public function setDefaultCartridgesAlarmThreshold(int $defaultCartridgesAlarmThreshold): self
    {
        $this->default_cartridges_alarm_threshold = $defaultCartridgesAlarmThreshold;

        return $this;
    }

    public function getDefaultConsumablesAlarmThreshold(): ?int
    {
        return $this->default_consumables_alarm_threshold;
    }

    public function setDefaultConsumablesAlarmThreshold(int $defaultConsumablesAlarmThreshold): self
    {
        $this->default_consumables_alarm_threshold = $defaultConsumablesAlarmThreshold;

        return $this;
    }

    public function getDelaySendEmails(): ?int
    {
        return $this->delay_send_emails;
    }

    public function setDelaySendEmails(int $delaySendEmails): self
    {
        $this->delay_send_emails = $delaySendEmails;

        return $this;
    }

    public function getIsNotifEnableDefault(): ?int
    {
        return $this->is_notif_enable_default;
    }

    public function setIsNotifEnableDefault(int $isNotifEnableDefault): self
    {
        $this->is_notif_enable_default = $isNotifEnableDefault;

        return $this;
    }

    public function getInquestDuration(): ?int
    {
        return $this->inquest_duration;
    }

    public function setInquestDuration(int $inquestDuration): self
    {
        $this->inquest_duration = $inquestDuration;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->date_mod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    public function getAutofillDecommissionDate(): ?string
    {
        return $this->autofill_decommission_date;
    }

    public function setAutofillDecommissionDate(string $autofillDecommissionDate): self
    {
        $this->autofill_decommission_date = $autofillDecommissionDate;

        return $this;
    }

    public function getSuppliersAsPrivate(): ?int
    {
        return $this->suppliers_as_private;
    }

    public function setSuppliersAsPrivate(int $suppliersAsPrivate): self
    {
        $this->suppliers_as_private = $suppliersAsPrivate;

        return $this;
    }

    public function getAnonymizeSupportAgents(): ?int
    {
        return $this->anonymize_support_agents;
    }

    public function setAnonymizeSupportAgents(int $anonymizeSupportAgents): self
    {
        $this->anonymize_support_agents = $anonymizeSupportAgents;

        return $this;
    }

    public function getEnableCustomCss(): ?int
    {
        return $this->enable_custom_css;
    }

    public function setEnableCustomCss(int $enableCustomCss): self
    {
        $this->enable_custom_css = $enableCustomCss;

        return $this;
    }

    public function getCustomCssCode(): ?string
    {
        return $this->custom_css_code;
    }

    public function setCustomCssCode(string $customCssCode): self
    {
        $this->custom_css_code = $customCssCode;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAltitude(): ?string
    {
        return $this->altitude;
    }

    public function setAltitude(string $altitude): self
    {
        $this->altitude = $altitude;

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
     * Get the value of calendar
     */ 
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set the value of calendar
     *
     * @return  self
     */ 
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Get the value of tickettemplate
     */ 
    public function getTickettemplate()
    {
        return $this->tickettemplate;
    }

    /**
     * Set the value of tickettemplate
     *
     * @return  self
     */ 
    public function setTickettemplate($tickettemplate)
    {
        $this->tickettemplate = $tickettemplate;

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

    /**
     * Get the value of authldap
     */ 
    public function getAuthldap()
    {
        return $this->authldap;
    }

    /**
     * Set the value of authldap
     *
     * @return  self
     */ 
    public function setAuthldap($authldap)
    {
        $this->authldap = $authldap;

        return $this;
    }

    /**
     * Get the value of entityKnowbaseitems
     */ 
    public function getEntityKnowbaseitems()
    {
        return $this->entityKnowbaseitems;
    }

    /**
     * Set the value of entityKnowbaseitems
     *
     * @return  self
     */ 
    public function setEntityKnowbaseitems($entityKnowbaseitems)
    {
        $this->entityKnowbaseitems = $entityKnowbaseitems;

        return $this;
    }
}
