<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notificationtemplatetranslations')]
#[ORM\Index(name: 'notificationtemplates_id', columns: ['notificationtemplates_id'])]
class Notificationtemplatetranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Notificationtemplate::class)]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Notificationtemplate $notificationtemplate = null;

    #[ORM\Column(name: 'language', type: 'string', length: 10, options: ['default' => ''])]
    private $language;

    #[ORM\Column(name: 'subject', type: 'string', length: 255)]
    private $subject;

    #[ORM\Column(name: 'content_text', type: 'text', length: 65535, nullable: true)]
    private $contentText;

    #[ORM\Column(name: 'content_html', type: 'text', length: 65535, nullable: true)]
    private $contentHtml;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

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

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setContentText(?string $contentText): self
    {
        $this->contentText = $contentText;

        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }


    /**
     * Get the value of notificationtemplate
     */
    public function getNotificationtemplate()
    {
        return $this->notificationtemplate;
    }

    /**
     * Set the value of notificationtemplate
     *
     * @return  self
     */
    public function setNotificationtemplate($notificationtemplate)
    {
        $this->notificationtemplate = $notificationtemplate;

        return $this;
    }
}
