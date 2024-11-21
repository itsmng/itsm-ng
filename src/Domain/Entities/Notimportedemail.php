<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notimportedemails')]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'mailcollectors_id', columns: ['mailcollectors_id'])]
class Notimportedemail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $from;

    #[ORM\Column(type: 'string', length: 255)]
    private $to;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $mailcollectors_id;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $date;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $subject;

    #[ORM\Column(type: 'string', length: 255)]
    private $messageid;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $reason;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getMailcollectorsId(): ?int
    {
        return $this->mailcollectors_id;
    }

    public function setMailcollectorsId(?int $mailcollectors_id): self
    {
        $this->mailcollectors_id = $mailcollectors_id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessageid(): ?string
    {
        return $this->messageid;
    }

    public function setMessageid(?string $messageid): self
    {
        $this->messageid = $messageid;

        return $this;
    }

    public function getReason(): ?int
    {
        return $this->reason;
    }

    public function setReason(?int $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

}
