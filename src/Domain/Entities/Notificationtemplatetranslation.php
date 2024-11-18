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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $notificationtemplates_id;
    
    #[ORM\Column(type: 'string', length: 10, options: ['default' => ''])]
    private $language;
    
    #[ORM\Column(type: 'string', length: 255)]
    private $subject;
    
    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $content_text;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $content_html;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotificationtemplatesId(): ?int
    {
        return $this->notificationtemplates_id;
    }

    public function setNotificationtemplatesId(?int $notificationtemplates_id): self
    {
        $this->notificationtemplates_id = $notificationtemplates_id;

        return $this;
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
        return $this->content_text;
    }

    public function setContentText(?string $content_text): self
    {
        $this->content_text = $content_text;

        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->content_html;
    }

    public function setContentHtml(?string $content_html): self
    {
        $this->content_html = $content_html;

        return $this;
    }

}
