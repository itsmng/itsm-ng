<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notimportedemails')]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'mailcollectors_id', columns: ['mailcollectors_id'])]
#[ORM\Index(name: 'date', columns: ['date'])]
class NotImportedEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'from', type: 'string', length: 255)]
    private $from;

    #[ORM\Column(name: 'to', type: 'string', length: 255)]
    private $to;

    #[ORM\ManyToOne(targetEntity: Mailcollector::class)]
    #[ORM\JoinColumn(name: 'mailcollectors_id', referencedColumnName: 'id', nullable: true)]
    private ?Mailcollector $mailcollector = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: false)]
    private $date;

    #[ORM\Column(name: 'subject', type: 'text', length: 65535, nullable: true)]
    private $subject;

    #[ORM\Column(name: 'messageid', type: 'string', length: 255)]
    private $messageid;

    #[ORM\Column(name: 'reason', type: 'integer', options: ['default' => 0])]
    private $reason;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

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

    /**
     * Get the value of mailcollector
     */
    public function getMailcollector()
    {
        return $this->mailcollector;
    }

    /**
     * Set the value of mailcollector
     *
     * @return  self
     */
    public function setMailcollector($mailcollector)
    {
        $this->mailcollector = $mailcollector;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
