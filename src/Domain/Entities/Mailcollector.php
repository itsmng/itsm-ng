<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_mailcollectors')]
#[ORM\Index(name: "is_active", columns: ['is_active'])]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
class Mailcollector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'host', type: 'string', length: 255, nullable: true)]
    private $host;

    #[ORM\Column(name: 'login', type: 'string', length: 255, nullable: true)]
    private $login;

    #[ORM\Column(name: 'filesize_max', type: 'integer', options: ['default' => 2097152])]
    private $filesizeMax;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private $isActive;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'passwd', type: 'string', length: 255, nullable: true)]
    private $passwd;

    #[ORM\Column(name: 'accepted', type: 'string', length: 255, nullable: true)]
    private $accepted;

    #[ORM\Column(name: 'refused', type: 'string', length: 255, nullable: true)]
    private $refused;

    #[ORM\Column(name: 'errors', type: 'integer', options: ['default' => 0])]
    private $errors;

    #[ORM\Column(name: 'use_mail_date', type: 'boolean', options: ['default' => false])]
    private $useMailDate;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'requester_field', type: 'integer', options: ['default' => 0])]
    private $requesterField;

    #[ORM\Column(name: 'add_cc_to_observer', type: 'boolean', options: ['default' => false])]
    private $addCcToObserver;

    #[ORM\Column(name: 'collect_only_unread', type: 'boolean', options: ['default' => false])]
    private $collectOnlyUnread;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getFilesizeMax(): ?int
    {
        return $this->filesizeMax;
    }

    public function setFilesizeMax(int $filesizeMax): self
    {
        $this->filesizeMax = $filesizeMax;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPasswd(): ?string
    {
        return $this->passwd;
    }

    public function setPasswd(string $passwd): self
    {
        $this->passwd = $passwd;

        return $this;
    }

    public function getAccepted(): ?string
    {
        return $this->accepted;
    }

    public function setAccepted(string $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getRefused(): ?string
    {
        return $this->refused;
    }

    public function setRefused(string $refused): self
    {
        $this->refused = $refused;

        return $this;
    }

    public function getErrors(): ?int
    {
        return $this->errors;
    }

    public function setErrors(int $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getUseMailDate(): ?bool
    {
        return $this->useMailDate;
    }

    public function setUseMailDate(bool $useMailDate): self
    {
        $this->useMailDate = $useMailDate;

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

    public function getRequesterField(): ?int
    {
        return $this->requesterField;
    }

    public function setRequesterField(int $requesterField): self
    {
        $this->requesterField = $requesterField;

        return $this;
    }

    public function getAddCcToObserver(): ?bool
    {
        return $this->addCcToObserver;
    }

    public function setAddCcToObserver(bool $addCcToObserver): self
    {
        $this->addCcToObserver = $addCcToObserver;

        return $this;
    }

    public function getCollectOnlyUnread(): ?bool
    {
        return $this->collectOnlyUnread;
    }

    public function setCollectOnlyUnread(bool $collectOnlyUnread): self
    {
        $this->collectOnlyUnread = $collectOnlyUnread;

        return $this;
    }
}
