<?php

namespace App\Entity;

use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LinkRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table("links")
 */
class Link
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
     * @ORM\Column(type="string", length=16, unique=true)
     * @Assert\Length(max=16)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=2083)
     * @Assert\NotBlank()
     * @Assert\Length(max=2083)
     * @Assert\Url()
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=16)
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"direct", "interstitial", "iframe"})
     */
    private $mode = 'direct';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    private $metaTitle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    private $metaDescription;

    /**
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="links")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Click", mappedBy="link", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt"="DESC"})
     * @var ArrayCollection
     */
    private $clicks;

    public function __construct()
    {
        $this->clicks = new ArrayCollection();
    }

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $title): self
    {
        $this->metaTitle = $title;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $description): self
    {
        $this->metaDescription = $description;
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

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getClicks(): Collection
    {
        return $this->clicks;
    }

    public function addClick(Click $click): self
    {
        $this->clicks[] = $click;
        $click->setLink($this);
        return $this;
    }
}
