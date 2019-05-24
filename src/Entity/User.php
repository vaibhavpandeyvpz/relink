<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table("users")
 * @UniqueEntity("email", groups={"Creation"})
 */
class User implements UserInterface
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=6, max=180)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Assert\Choice(choices={"ROLE_USER", "ROLE_ADMIN"}, groups={"ACL"}, multiple=true)
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(min=8, max=32)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="PasswordResetToken", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt"="DESC"})
     * @var ArrayCollection
     */
    private $passwordResetTokens;

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="user", cascade={"persist"})
     * @ORM\OrderBy({"createdAt"="DESC"})
     * @var ArrayCollection
     */
    private $links;

    public function __construct()
    {
        $this->passwordResetTokens = new ArrayCollection();
        $this->links = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPasswordResetTokens(): Collection
    {
        return $this->passwordResetTokens;
    }

    public function addPasswordResetToken(PasswordResetToken $token): self
    {
        $this->passwordResetTokens[] = $token;
        $token->setUser($this);
        return $this;
    }

    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        $this->links[] = $link;
        $link->setUser($this);
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
    }
}
