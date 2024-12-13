<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changevalidations')]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'users_id_validate', columns: ['users_id_validate'])]
#[ORM\Index(name: 'changes_id', columns: ['changes_id'])]
#[ORM\Index(name: 'submission_date', columns: ['submission_date'])]
#[ORM\Index(name: 'validation_date', columns: ['validation_date'])]
#[ORM\Index(name: 'status', columns: ['status'])]

class Changevalidation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_validate;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment_submission;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment_validation;

    #[ORM\Column(type: 'integer', options: ['default' => 2])]
    private $status;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private $submission_date;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private $validation_date;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $timeline_position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?int
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getChangesId(): ?int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

        return $this;
    }

    public function getUsersIdValidate(): ?int
    {
        return $this->users_id_validate;
    }

    public function setUsersIdValidate(int $users_id_validate): self
    {
        $this->users_id_validate = $users_id_validate;

        return $this;
    }

    public function getCommentSubmission(): ?string
    {
        return $this->comment_submission;
    }

    public function setCommentSubmission(?string $comment_submission): self
    {
        $this->comment_submission = $comment_submission;

        return $this;
    }

    public function getCommentValidation(): ?string
    {
        return $this->comment_validation;
    }

    public function setCommentValidation(?string $comment_validation): self
    {
        $this->comment_validation = $comment_validation;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submission_date;
    }

    public function setSubmissionDate(\DateTimeInterface $submission_date): self
    {
        $this->submission_date = $submission_date;

        return $this;
    }

    public function getValidationDate(): ?\DateTimeInterface
    {
        return $this->validation_date;
    }

    public function setValidationDate(\DateTimeInterface $validation_date): self
    {
        $this->validation_date = $validation_date;

        return $this;
    }

    public function getTimelinePosition(): ?int
    {
        return $this->timeline_position;
    }

    public function setTimelinePosition(int $timeline_position): self
    {
        $this->timeline_position = $timeline_position;

        return $this;
    }
}
