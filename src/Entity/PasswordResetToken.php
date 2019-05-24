<?php

namespace App\Entity;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PasswordResetTokenRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table("password_reset_tokens")
 */
class PasswordResetToken
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $id;

    /**
     * @ORM\Column(name="user_id", type="integer", options={"unsigned": true})
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="passwordResetTokens")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var User|null
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $id): self
    {
        $this->userId = $id;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getExpiresAtChronos(): ?Chronos
    {
        return $this->expiresAt ? new Chronos($this->expiresAt->format(DATE_ISO8601)) : null;
    }

    public function setExpiresAt(?\DateTimeInterface $timestamp): self
    {
        $this->expiresAt = $timestamp;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
