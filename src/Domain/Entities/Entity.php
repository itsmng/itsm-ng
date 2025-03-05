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
    #[ORM\Column(name: 'id', type: 'integer', options: ['default' => 0])]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'completename', type: 'text', nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(name: 'sons_cache', type: 'text', nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'ancestors_cache', type: 'text', nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'address', type: 'text', nullable: true, length: 65535)]
    private $address;

    #[ORM\Column(name: 'postcode', type: 'string', length: 255, nullable: true)]
    private $postcode;

    #[ORM\Column(name: 'town', type: 'string', length: 255, nullable: true)]
    private $town;

    #[ORM\Column(name: 'state', type: 'string', length: 255, nullable: true)]
    private $state;

    #[ORM\Column(name: 'country', type: 'string', length: 255, nullable: true)]
    private $country;

    #[ORM\Column(name: 'website', type: 'string', length: 255, nullable: true)]
    private $website;

    #[ORM\Column(name: 'phonenumber', type: 'string', length: 255, nullable: true)]
    private $phonenumber;

    #[ORM\Column(name: 'fax', type: 'string', length: 255, nullable: true)]
    private $fax;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\Column(name: 'admin_email', type: 'string', length: 255, nullable: true)]
    private $adminEmail;

    #[ORM\Column(name: 'admin_email_name', type: 'string', length: 255, nullable: true)]
    private $adminEmailName;

    #[ORM\Column(name: 'admin_reply', type: 'string', length: 255, nullable: true)]
    private $adminReply;

    #[ORM\Column(name: 'admin_reply_name', type: 'string', length: 255, nullable: true)]
    private $adminReplyName;

    #[ORM\Column(name: 'notification_subject_tag', type: 'string', length: 255, nullable: true)]
    private $notificationSubjectTag;

    #[ORM\Column(name: 'ldap_dn', type: 'string', length: 255, nullable: true)]
    private $ldapDn;

    #[ORM\Column(name: 'tag', type: 'string', length: 255, nullable: true)]
    private $tag;

    #[ORM\ManyToOne(targetEntity: AuthLdap::class)]
    #[ORM\JoinColumn(name: 'authldaps_id', referencedColumnName: 'id', nullable: true)]
    private ?AuthLdap $authldap = null;

    #[ORM\Column(name: 'mail_domain', type: 'string', length: 255, nullable: true)]
    private $mailDomain;

    #[ORM\Column(name: 'entity_ldapfilter', type: 'text', nullable: true, length: 65535)]
    private $entityLdapfilter;

    #[ORM\Column(name: 'mailing_signature', type: 'text', nullable: true, length: 65535)]
    private $mailingSignature;

    #[ORM\Column(name: 'cartridges_alert_repeat', type: 'integer', options: ['default' => -2])]
    private $cartridgesAlertRepeat;

    #[ORM\Column(name: 'consumables_alert_repeat', type: 'integer', options: ['default' => -2])]
    private $consumablesAlertRepeat;

    #[ORM\Column(name: 'use_licenses_alert', type: 'integer', options: ['default' => -2])]
    private $useLicensesAlert;

    #[ORM\Column(name: 'send_licenses_alert_before_delay', type: 'integer', options: ['default' => -2])]
    private $sendLicensesAlertBeforeDelay;

    #[ORM\Column(name: 'use_certificates_alert', type: 'integer', options: ['default' => -2])]
    private $useCertificatesAlert;

    #[ORM\Column(name: 'send_certificates_alert_before_delay', type: 'integer', options: ['default' => -2])]
    private $sendCertificatesAlertBeforeDelay;

    #[ORM\Column(name: 'use_contracts_alert', type: 'integer', options: ['default' => -2])]
    private $useContractsAlert;

    #[ORM\Column(name: 'send_contracts_alert_before_delay', type: 'integer', options: ['default' => -2])]
    private $sendContractsAlertBeforeDelay;

    #[ORM\Column(name: 'use_infocoms_alert', type: 'integer', options: ['default' => -2])]
    private $useInfocomsAlert;

    #[ORM\Column(name: 'send_infocoms_alert_before_delay', type: 'integer', options: ['default' => -2])]
    private $sendInfocomsAlertBeforeDelay;

    #[ORM\Column(name: 'use_reservations_alert', type: 'integer', options: ['default' => -2])]
    private $useReservationsAlert;

    #[ORM\Column(name: 'use_domains_alert', type: 'integer', options: ['default' => -2])]
    private $useDomainsAlert;

    #[ORM\Column(name: 'send_domains_alert_close_expiries_delay', type: 'integer', options: ['default' => -2])]
    private $sendDomainsAlertCloseExpiriesDelay;

    #[ORM\Column(name: 'send_domains_alert_expired_delay', type: 'integer', options: ['default' => -2])]
    private $sendDomainsAlertExpiredDelay;

    #[ORM\Column(name: 'autoclose_delay', type: 'integer', options: ['default' => -2])]
    private $autocloseDelay;

    #[ORM\Column(name: 'autopurge_delay', type: 'integer', options: ['default' => -10])]
    private $autopurgeDelay;

    #[ORM\Column(name: 'notclosed_delay', type: 'integer', options: ['default' => -2])]
    private $notclosedDelay;

    #[ORM\ManyToOne(targetEntity: Calendar::class)]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: true)]
    private ?Calendar $calendar = null;

    #[ORM\Column(name: 'auto_assign_mode', type: 'integer', options: ['default' => -2])]
    private $autoAssignMode;

    #[ORM\Column(name: 'tickettype', type: 'integer', options: ['default' => -2])]
    private $tickettype;

    #[ORM\Column(name: 'max_closedate', type: 'datetime', nullable: true)]
    private $maxClosedate;

    #[ORM\Column(name: 'inquest_config', type: 'integer', options: ['default' => -2])]
    private $inquestConfig;

    #[ORM\Column(name: 'inquest_rate', type: 'integer', options: ['default' => 0])]
    private $inquestRate;

    #[ORM\Column(name: 'inquest_delay', type: 'integer', options: ['default' => -10])]
    private $inquestDelay;

    #[ORM\Column(name: 'inquest_url', type: 'string', length: 255, nullable: true)]
    private $inquestURL;

    #[ORM\Column(name: 'autofill_warranty_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillWarrantyDate;

    #[ORM\Column(name: 'autofill_use_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillUseDate;

    #[ORM\Column(name: 'autofill_buy_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillBuyDate;

    #[ORM\Column(name: 'autofill_delivery_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillDeliveryDate;

    #[ORM\Column(name: 'autofill_order_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillOrderDate;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplate = null;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ChangeTemplate $changetemplate = null;

    #[ORM\ManyToOne(targetEntity: Problemtemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Problemtemplate $problemtemplate = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id_software', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entitySoftware = null;

    #[ORM\Column(name: 'default_contract_alert', type: 'integer', options: ['default' => -2])]
    private $defaultContractAlert;

    #[ORM\Column(name: 'default_infocom_alert', type: 'integer', options: ['default' => -2])]
    private $defaultInfocomAlert;

    #[ORM\Column(name: 'default_cartridges_alarm_threshold', type: 'integer', options: ['default' => -2])]
    private $defaultCartridgesAlarmThreshold;

    #[ORM\Column(name: 'default_consumables_alarm_threshold', type: 'integer', options: ['default' => -2])]
    private $defaultConsumablesAlarmThreshold;

    #[ORM\Column(name: 'delay_send_emails', type: 'integer', options: ['default' => -2])]
    private $delaySendEmails;

    #[ORM\Column(name: 'is_notif_enable_default', type: 'integer', options: ['default' => -2])]
    private $isNotifEnableDefault;

    #[ORM\Column(name: 'inquest_duration', type: 'integer', options: ['default' => 0])]
    private $inquestDuration;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    #[ORM\Column(name: 'autofill_decommission_date', type: 'string', length: 255, options: ['default' => '-2'])]
    private $autofillDecommissionDate;

    #[ORM\Column(name: 'suppliers_as_private', type: 'integer', options: ['default' => -2])]
    private $suppliersAsPrivate;

    #[ORM\Column(name: 'anonymize_support_agents', type: 'integer', options: ['default' => -2])]
    private $anonymizeSupportAgents;

    #[ORM\Column(name: 'enable_custom_css', type: 'integer', options: ['default' => -2])]
    private $enableCustomCss;

    #[ORM\Column(name: 'custom_css_code', type: 'text', nullable: true, length: 65535)]
    private $customCssCode;

    #[ORM\Column(name: 'latitude', type: 'string', length: 255, nullable: true)]
    private $latitude;

    #[ORM\Column(name: 'longitude', type: 'string', length: 255, nullable: true)]
    private $longitude;

    #[ORM\Column(name: 'altitude', type: 'string', length: 255, nullable: true)]
    private $altitude;

    #[ORM\OneToMany(mappedBy: 'entity', targetEntity: EntityKnowbaseitem::class)]
    private Collection $entityKnowbaseitems;

    #[ORM\OneToMany(mappedBy: 'entity', targetEntity: EntityReminder::class)]
    private Collection $entityReminders;

    #[ORM\OneToMany(mappedBy: 'entity', targetEntity: EntityRssFeed::class)]
    private Collection $entityRssfeeds;

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
        return $this->sonsCache;
    }

    public function setSonsCache(string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

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
        return $this->adminEmail;
    }

    public function setAdminEmail(string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    public function getAdminEmailName(): ?string
    {
        return $this->adminEmailName;
    }

    public function setAdminEmailName(string $adminEmailName): self
    {
        $this->adminEmailName = $adminEmailName;

        return $this;
    }

    public function getAdminReply(): ?string
    {
        return $this->adminReply;
    }

    public function setAdminReply(string $adminReply): self
    {
        $this->adminReply = $adminReply;

        return $this;
    }

    public function getAdminReplyName(): ?string
    {
        return $this->adminReplyName;
    }

    public function setAdminReplyName(string $adminReplyName): self
    {
        $this->adminReplyName = $adminReplyName;

        return $this;
    }

    public function getNotificationSubjectTag(): ?string
    {
        return $this->notificationSubjectTag;
    }

    public function setNotificationSubjectTag(string $notificationSubjectTag): self
    {
        $this->notificationSubjectTag = $notificationSubjectTag;

        return $this;
    }

    public function getLdapDn(): ?string
    {
        return $this->ldapDn;
    }

    public function setLdapDn(string $ldapDn): self
    {
        $this->ldapDn = $ldapDn;

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


    public function getMailDomain(): ?string
    {
        return $this->mailDomain;
    }

    public function setMailDomain(string $mailDomain): self
    {
        $this->mailDomain = $mailDomain;

        return $this;
    }

    public function getEntityLdapfilter(): ?string
    {
        return $this->entityLdapfilter;
    }

    public function setEntityLdapfilter(string $entityLdapfilter): self
    {
        $this->entityLdapfilter = $entityLdapfilter;

        return $this;
    }

    public function getMailingSignature(): ?string
    {
        return $this->mailingSignature;
    }

    public function setMailingSignature(string $mailingSignature): self
    {
        $this->mailingSignature = $mailingSignature;

        return $this;
    }

    public function getCartridgesAlertRepeat(): ?int
    {
        return $this->cartridgesAlertRepeat;
    }

    public function setCartridgesAlertRepeat(int $cartridgesAlertRepeat): self
    {
        $this->cartridgesAlertRepeat = $cartridgesAlertRepeat;

        return $this;
    }

    public function getConsumablesAlertRepeat(): ?int
    {
        return $this->consumablesAlertRepeat;
    }

    public function setConsumablesAlertRepeat(int $consumablesAlertRepeat): self
    {
        $this->consumablesAlertRepeat = $consumablesAlertRepeat;

        return $this;
    }

    public function getUseLicensesAlert(): ?int
    {
        return $this->useLicensesAlert;
    }

    public function setUseLicensesAlert(int $useLicensesAlert): self
    {
        $this->useLicensesAlert = $useLicensesAlert;

        return $this;
    }

    public function getSendLicensesAlertBeforeDelay(): ?int
    {
        return $this->sendLicensesAlertBeforeDelay;
    }

    public function setSendLicensesAlertBeforeDelay(int $sendLicensesAlertBeforeDelay): self
    {
        $this->sendLicensesAlertBeforeDelay = $sendLicensesAlertBeforeDelay;

        return $this;
    }

    public function getUseCertificatesAlert(): ?int
    {
        return $this->useCertificatesAlert;
    }

    public function setUseCertificatesAlert(int $useCertificatesAlert): self
    {
        $this->useCertificatesAlert = $useCertificatesAlert;

        return $this;
    }

    public function getSendCertificatesAlertBeforeDelay(): ?int
    {
        return $this->sendCertificatesAlertBeforeDelay;
    }

    public function setSendCertificatesAlertBeforeDelay(int $sendCertificatesAlertBeforeDelay): self
    {
        $this->sendCertificatesAlertBeforeDelay = $sendCertificatesAlertBeforeDelay;

        return $this;
    }

    public function getUseContractsAlert(): ?int
    {
        return $this->useContractsAlert;
    }

    public function setUseContractsAlert(int $useContractsAlert): self
    {
        $this->useContractsAlert = $useContractsAlert;

        return $this;
    }

    public function getSendContractsAlertBeforeDelay(): ?int
    {
        return $this->sendContractsAlertBeforeDelay;
    }

    public function setSendContractsAlertBeforeDelay(int $sendContractsAlertBeforeDelay): self
    {
        $this->sendContractsAlertBeforeDelay = $sendContractsAlertBeforeDelay;

        return $this;
    }

    public function getUseInfocomsAlert(): ?int
    {
        return $this->useInfocomsAlert;
    }

    public function setUseInfocomsAlert(int $useInfocomsAlert): self
    {
        $this->useInfocomsAlert = $useInfocomsAlert;

        return $this;
    }

    public function getSendInfocomsAlertBeforeDelay(): ?int
    {
        return $this->sendInfocomsAlertBeforeDelay;
    }

    public function setSendInfocomsAlertBeforeDelay(int $sendInfocomsAlertBeforeDelay): self
    {
        $this->sendInfocomsAlertBeforeDelay = $sendInfocomsAlertBeforeDelay;

        return $this;
    }

    public function getUseReservationsAlert(): ?int
    {
        return $this->useReservationsAlert;
    }

    public function setUseReservationsAlert(int $useReservationsAlert): self
    {
        $this->useReservationsAlert = $useReservationsAlert;

        return $this;
    }

    public function getUseDomainsAlert(): ?int
    {
        return $this->useDomainsAlert;
    }

    public function setUseDomainsAlert(int $useDomainAlert): self
    {
        $this->useDomainsAlert = $useDomainAlert;

        return $this;
    }

    public function getSendDomainsAlertCloseExpiriesDelay(): ?int
    {
        return $this->sendDomainsAlertCloseExpiriesDelay;
    }

    public function setSendDomainsAlertCloseExpiriesDelay(int $sendDomainAlertCloseExpiriesDelay): self
    {
        $this->sendDomainsAlertCloseExpiriesDelay = $sendDomainAlertCloseExpiriesDelay;

        return $this;
    }

    public function getSendDomainsAlertExpiredDelay(): ?int
    {
        return $this->sendDomainsAlertExpiredDelay;
    }

    public function setSendDomainsAlertExpiredDelay(int $sendDomainAlertExpiredDelay): self
    {
        $this->sendDomainsAlertExpiredDelay = $sendDomainAlertExpiredDelay;

        return $this;
    }

    public function getAutocloseDelay(): ?int
    {
        return $this->autocloseDelay;
    }

    public function setAutocloseDelay(int $autocloseDelay): self
    {
        $this->autocloseDelay = $autocloseDelay;

        return $this;
    }

    public function getAutopurgeDelay(): ?int
    {
        return $this->autopurgeDelay;
    }

    public function setAutopurgeDelay(int $autopurgeDelay): self
    {
        $this->autopurgeDelay = $autopurgeDelay;

        return $this;
    }

    public function getNotclosedDelay(): ?int
    {
        return $this->notclosedDelay;
    }

    public function setNotclosedDelay(int $notclosedDelay): self
    {
        $this->notclosedDelay = $notclosedDelay;

        return $this;
    }

    public function getAutoAssignMode(): ?int
    {
        return $this->autoAssignMode;
    }

    public function setAutoAssignMode(int $autoAssignMode): self
    {
        $this->autoAssignMode = $autoAssignMode;

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
        return $this->maxClosedate;
    }

    public function setMaxClosedate(\DateTimeInterface $maxClosedate): self
    {
        $this->maxClosedate = $maxClosedate;

        return $this;
    }

    public function getInquestConfig(): ?int
    {
        return $this->inquestConfig;
    }

    public function setInquestConfig(int $inquestConfig): self
    {
        $this->inquestConfig = $inquestConfig;

        return $this;
    }

    public function getInquestRate(): ?int
    {
        return $this->inquestRate;
    }

    public function setInquestRate(int $inquestRate): self
    {
        $this->inquestRate = $inquestRate;

        return $this;
    }

    public function getInquestDelay(): ?int
    {
        return $this->inquestDelay;
    }

    public function setInquestDelay(int $inquestDelay): self
    {
        $this->inquestDelay = $inquestDelay;

        return $this;
    }

    public function getInquestURL(): ?string
    {
        return $this->inquestURL;
    }

    public function setInquestURL(string $inquestURL): self
    {
        $this->inquestURL = $inquestURL;

        return $this;
    }

    public function getAutofillWarrantyDate(): ?string
    {
        return $this->autofillWarrantyDate;
    }

    public function setAutofillWarrantyDate(string $autofillWarrantyDate): self
    {
        $this->autofillWarrantyDate = $autofillWarrantyDate;

        return $this;
    }

    public function getAutofillUseDate(): ?string
    {
        return $this->autofillUseDate;
    }

    public function setAutofillUseDate(string $autofillUseDate): self
    {
        $this->autofillUseDate = $autofillUseDate;

        return $this;
    }

    public function getAutofillBuyDate(): ?string
    {
        return $this->autofillBuyDate;
    }

    public function setAutofillBuyDate(string $autofillBuyDate): self
    {
        $this->autofillBuyDate = $autofillBuyDate;

        return $this;
    }

    public function getAutofillDeliveryDate(): ?string
    {
        return $this->autofillDeliveryDate;
    }

    public function setAutofillDeliveryDate(string $autofillDeliveryDate): self
    {
        $this->autofillDeliveryDate = $autofillDeliveryDate;

        return $this;
    }

    public function getAutofillOrderDate(): ?string
    {
        return $this->autofillOrderDate;
    }

    public function setAutofillOrderDate(string $autofillOrderDate): self
    {
        $this->autofillOrderDate = $autofillOrderDate;

        return $this;
    }

    public function getDefaultContractAlert(): ?int
    {
        return $this->defaultContractAlert;
    }

    public function setDefaultContractAlert(int $defaultContractAlert): self
    {
        $this->defaultContractAlert = $defaultContractAlert;

        return $this;
    }

    public function getDefaultInfocomAlert(): ?int
    {
        return $this->defaultInfocomAlert;
    }

    public function setDefaultInfocomAlert(int $defaultInfocomAlert): self
    {
        $this->defaultInfocomAlert = $defaultInfocomAlert;

        return $this;
    }

    public function getDefaultCartridgesAlarmThreshold(): ?int
    {
        return $this->defaultCartridgesAlarmThreshold;
    }

    public function setDefaultCartridgesAlarmThreshold(int $defaultCartridgesAlarmThreshold): self
    {
        $this->defaultCartridgesAlarmThreshold = $defaultCartridgesAlarmThreshold;

        return $this;
    }

    public function getDefaultConsumablesAlarmThreshold(): ?int
    {
        return $this->defaultConsumablesAlarmThreshold;
    }

    public function setDefaultConsumablesAlarmThreshold(int $defaultConsumablesAlarmThreshold): self
    {
        $this->defaultConsumablesAlarmThreshold = $defaultConsumablesAlarmThreshold;

        return $this;
    }

    public function getDelaySendEmails(): ?int
    {
        return $this->delaySendEmails;
    }

    public function setDelaySendEmails(int $delaySendEmails): self
    {
        $this->delaySendEmails = $delaySendEmails;

        return $this;
    }

    public function getIsNotifEnableDefault(): ?int
    {
        return $this->isNotifEnableDefault;
    }

    public function setIsNotifEnableDefault(int $isNotifEnableDefault): self
    {
        $this->isNotifEnableDefault = $isNotifEnableDefault;

        return $this;
    }

    public function getInquestDuration(): ?int
    {
        return $this->inquestDuration;
    }

    public function setInquestDuration(int $inquestDuration): self
    {
        $this->inquestDuration = $inquestDuration;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getAutofillDecommissionDate(): ?string
    {
        return $this->autofillDecommissionDate;
    }

    public function setAutofillDecommissionDate(string $autofillDecommissionDate): self
    {
        $this->autofillDecommissionDate = $autofillDecommissionDate;

        return $this;
    }

    public function getSuppliersAsPrivate(): ?int
    {
        return $this->suppliersAsPrivate;
    }

    public function setSuppliersAsPrivate(int $suppliersAsPrivate): self
    {
        $this->suppliersAsPrivate = $suppliersAsPrivate;

        return $this;
    }

    public function getAnonymizeSupportAgents(): ?int
    {
        return $this->anonymizeSupportAgents;
    }

    public function setAnonymizeSupportAgents(int $anonymizeSupportAgents): self
    {
        $this->anonymizeSupportAgents = $anonymizeSupportAgents;

        return $this;
    }

    public function getEnableCustomCss(): ?int
    {
        return $this->enableCustomCss;
    }

    public function setEnableCustomCss(int $enableCustomCss): self
    {
        $this->enableCustomCss = $enableCustomCss;

        return $this;
    }

    public function getCustomCssCode(): ?string
    {
        return $this->customCssCode;
    }

    public function setCustomCssCode(string $customCssCode): self
    {
        $this->customCssCode = $customCssCode;

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

    /**
     * Get the value of entityReminders
     */
    public function getEntityReminders()
    {
        return $this->entityReminders;
    }

    /**
     * Set the value of entityReminders
     *
     * @return  self
     */
    public function setEntityReminders($entityReminders)
    {
        $this->entityReminders = $entityReminders;

        return $this;
    }

    /**
     * Get the value of entityRssfeeds
     */
    public function getEntityRssfeeds()
    {
        return $this->entityRssfeeds;
    }

    /**
     * Set the value of entityRssfeeds
     *
     * @return  self
     */
    public function setEntityRssfeeds($entityRssfeeds)
    {
        $this->entityRssfeeds = $entityRssfeeds;

        return $this;
    }

    /**
     * Get the value of entitySoftware
     */
    public function getEntitySoftware()
    {
        return $this->entitySoftware;
    }

    /**
     * Set the value of entitySoftware
     *
     * @return  self
     */
    public function setEntitySoftware($entitySoftware)
    {
        $this->entitySoftware = $entitySoftware;

        return $this;
    }
}
