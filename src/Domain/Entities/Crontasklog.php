<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_crontasklogs')]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Index(name: 'crontasks_id', columns: ['crontasks_id'])]
#[ORM\Index(name: 'crontasklogs_id_state', columns: ['crontasklogs_id', 'state'])]

class Crontasklog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'crontasks_id')]
    private $crontasks_id;

    #[ORM\ManyToOne(targetEntity: Crontask::class)]
    #[ORM\JoinColumn(name: 'crontasks_id', referencedColumnName: 'id', nullable: false)]
    private ?Crontask $crontask;

    #[ORM\Column(type: 'integer', name: 'crontasklogs_id', options: ['comment' => 'id of "start" event'])]
    private $crontasklogs_id;

    #[ORM\ManyToOne(targetEntity: Crontasklog::class)]
    #[ORM\JoinColumn(name: 'crontasklogs_id', referencedColumnName: 'id', nullable: false)]
    private ?Crontasklog $crontasklogs;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $date;

    #[ORM\Column(type: 'integer', options: ['comment' => '0:start, 1:run, 2:stop'])]
    private $state;

    #[ORM\Column(type: 'float', options: ['comment' => 'time elapsed since start'])]
    private $elapsed;

    #[ORM\Column(type: 'integer', options: ['comment' => 'for statistics'])]
    private $volume;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['comment' => 'message'])]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCrontasksId(): ?int
    {
        return $this->crontasks_id;
    }

    public function setCrontasksId(int $crontasks_id): self
    {
        $this->crontasks_id = $crontasks_id;

        return $this;
    }

    public function getCrontasklogsId(): ?int
    {
        return $this->crontasklogs_id;
    }

    public function setCrontasklogsId(int $crontasklogs_id): self
    {
        $this->crontasklogs_id = $crontasklogs_id;

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

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getElapsed(): ?float
    {
        return $this->elapsed;
    }

    public function setElapsed(float $elapsed): self
    {
        $this->elapsed = $elapsed;

        return $this;
    }

    public function getVolume(): ?int
    {
        return $this->volume;
    }

    public function setVolume(int $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of crontask
     */
    public function getCrontask()
    {
        return $this->crontask;
    }

    /**
     * Set the value of crontask
     *
     * @return  self
     */
    public function setCrontask($crontask)
    {
        $this->crontask = $crontask;

        return $this;
    }

    /**
     * Get the value of crontasklogs
     */
    public function getCrontasklogs()
    {
        return $this->crontasklogs;
    }

    /**
     * Set the value of crontasklogs
     *
     * @return  self
     */
    public function setCrontasklogs($crontasklogs)
    {
        $this->crontasklogs = $crontasklogs;

        return $this;
    }
}
