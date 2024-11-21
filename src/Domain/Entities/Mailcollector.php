<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_mailcollectors')]
#[ORM\Index(columns: ['is_active'])]
#[ORM\Index(columns: ['date_mod'])]
#[ORM\Index(columns: ['date_creation'])]
class Mailcollector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $host;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $login;

    #[ORM\Column(type: 'integer', options: ['default' => 2097152])]
    private $filesize_max;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $is_active;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $passwd;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $accepted;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $refused;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $errors;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $use_mail_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $requester_field;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $add_cc_to_observer;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $collect_only_unread;

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
        return $this->filesize_max;
    }

    public function setFilesizeMax(int $filesize_max): self
    {
        $this->filesize_max = $filesize_max;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
        return $this->use_mail_date;
    }

    public function setUseMailDate(bool $use_mail_date): self
    {
        $this->use_mail_date = $use_mail_date;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getRequesterField(): ?int
    {
        return $this->requester_field;
    }

    public function setRequesterField(int $requester_field): self
    {
        $this->requester_field = $requester_field;

        return $this;
    }

    public function getAddCcToObserver(): ?bool
    {
        return $this->add_cc_to_observer;
    }

    public function setAddCcToObserver(bool $add_cc_to_observer): self
    {
        $this->add_cc_to_observer = $add_cc_to_observer;

        return $this;
    }

    public function getCollectOnlyUnread(): ?bool
    {
        return $this->collect_only_unread;
    }

    public function setCollectOnlyUnread(bool $collect_only_unread): self
    {
        $this->collect_only_unread = $collect_only_unread;

        return $this;
    }
}
