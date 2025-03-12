<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notificationchatconfigs')]
class NotificationChatConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'hookurl', type: 'string', length: 255, nullable: true)]
    private $hookurl;

    #[ORM\Column(name: 'chat', type: 'string', length: 255, nullable: true)]
    private $chat;

    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\Column(name: 'value', type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHookurl(): ?string
    {
        return $this->hookurl;
    }

    public function setHookurl(?string $hookurl): self
    {
        $this->hookurl = $hookurl;

        return $this;
    }

    public function getChat(): ?string
    {
        return $this->chat;
    }

    public function setChat(?string $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

}
