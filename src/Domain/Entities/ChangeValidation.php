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

class ChangeValidation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Change::class)]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: true)]
    private ?Change $change = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_validate', referencedColumnName: 'id', nullable: true)]
    private ?User $userValidate = null;

    #[ORM\Column(name: 'comment_submission', type: 'text', length: 65535, nullable: true)]
    private $commentSubmission;

    #[ORM\Column(name: 'comment_validation', type: 'text', length: 65535, nullable: true)]
    private $commentValidation;

    #[ORM\Column(name: 'status', type: 'integer', options: ['default' => 2])]
    private $status;

    #[ORM\Column(name: 'submission_date', type: 'datetime', nullable: false)]
    private $submissionDate;

    #[ORM\Column(name: 'validation_date', type: 'datetime', nullable: false)]
    private $validationDate;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getIsRecursive(): ?int
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(int $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(\DateTimeInterface $submissionDate): self
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    public function getValidationDate(): ?\DateTimeInterface
    {
        return $this->validationDate;
    }

    public function setValidationDate(\DateTimeInterface $validationDate): self
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    public function getTimelinePosition(): ?int
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(int $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

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

    /**
     * Get the value of change
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Set the value of change
     *
     * @return  self
     */
    public function setChange($change)
    {
        $this->change = $change;

        return $this;
    }


    /**
     * Get the value of userValidate
     */
    public function getUserValidate()
    {
        return $this->userValidate;
    }

    /**
     * Set the value of userValidate
     *
     * @return  self
     */
    public function setUserValidate($userValidate)
    {
        $this->userValidate = $userValidate;

        return $this;
    }
}
