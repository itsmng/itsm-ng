<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ticketvalidations")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "users_id_validate", columns: ["users_id_validate"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "submission_date", columns: ["submission_date"])]
#[ORM\Index(name: "validation_date", columns: ["validation_date"])]
#[ORM\Index(name: "status", columns: ["status"])]
class TicketValidation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId;

    #[ORM\Column(name: 'tickets_id', type: 'integer', options: ['default' => 0])]
    private $ticketsId;

    #[ORM\Column(name: 'users_id_validate', type: 'integer', options: ['default' => 0])]
    private $usersIdValidate;

    #[ORM\Column(name: 'comment_submission', type: 'text', length: 65535, nullable: true)]
    private $commentSubmission;

    #[ORM\Column(name: 'comment_validation', type: 'text', length: 65535, nullable: true)]
    private $commentValidation;

    #[ORM\Column(name: 'status', type: 'integer', options: ['default' => 2])]
    private $status;

    #[ORM\Column(name: 'submission_date', type: 'datetime', nullable: true)]
    private $submissionDate;

    #[ORM\Column(name: 'validation_date', type: 'datetime', nullable: true)]
    private $validationDate;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(?int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): self
    {
        $this->usersId = $usersId;

        return $this;
    }

    public function getTicketsId(): ?int
    {
        return $this->ticketsId;
    }

    public function setTicketsId(?int $ticketsId): self
    {
        $this->ticketsId = $ticketsId;

        return $this;
    }

    public function getUsersIdValidate(): ?int
    {
        return $this->usersIdValidate;
    }

    public function setUsersIdValidate(?int $usersIdValidate): self
    {
        $this->usersIdValidate = $usersIdValidate;

        return $this;
    }

    public function getCommentSubmission(): ?string
    {
        return $this->commentSubmission;
    }

    public function setCommentSubmission(?string $commentSubmission): self
    {
        $this->commentSubmission = $commentSubmission;

        return $this;
    }

    public function getCommentValidation(): ?string
    {
        return $this->commentValidation;
    }

    public function setCommentValidation(?string $commentValidation): self
    {
        $this->commentValidation = $commentValidation;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTime
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(?\DateTime $submissionDate): self
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    public function getValidationDate(): ?\DateTime
    {
        return $this->validationDate;
    }

    public function setValidationDate(?\DateTime $validationDate): self
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    public function getTimelinePosition(): ?bool
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(?bool $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

        return $this;
    }

}
