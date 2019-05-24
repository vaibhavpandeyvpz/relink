<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClickRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table("clicks")
 */
class Click
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $id;

    /**
     * @ORM\Column(name="link_id", type="integer", options={"unsigned": true})
     */
    private $linkId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $browser;

    /**
     * @ORM\Column(name="browser_name", type="string", length=255, nullable=true)
     */
    private $browserName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $browserVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $platform;

    /**
     * @ORM\Column(name="platform_name", type="string", length=255, nullable=true)
     */
    private $platformName;

    /**
     * @ORM\Column(name="platform_version", type="string", length=255, nullable=true)
     */
    private $platformVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $device;

    /**
     * @ORM\Column(name="device_brand", type="string", length=255, nullable=true)
     */
    private $deviceBrand;

    /**
     * @ORM\Column(name="device_model", type="string", length=255, nullable=true)
     */
    private $deviceModel;

    /**
     * @ORM\Column(name="ip_address", type="string", length=45)
     */
    private $ipAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Link", inversedBy="clicks")
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     * @var Link
     */
    private $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkId(): ?int
    {
        return $this->linkId;
    }

    public function setLinkId(int $id): self
    {
        $this->linkId = $id;
        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;
        return $this;
    }

    public function getBrowserName(): ?string
    {
        return $this->browserName;
    }

    public function setBrowserName(?string $name): self
    {
        $this->browserName = $name;
        return $this;
    }

    public function getBrowserVersion(): ?string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(?string $version): self
    {
        $this->browserVersion = $version;
        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(?string $platform): self
    {
        $this->platform = $platform;
        return $this;
    }

    public function getPlatformName(): ?string
    {
        return $this->platformName;
    }

    public function setPlatformName(?string $name): self
    {
        $this->platformName = $name;
        return $this;
    }

    public function getPlatformVersion(): ?string
    {
        return $this->platformVersion;
    }

    public function setPlatformVersion(?string $version): self
    {
        $this->platformVersion = $version;
        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $brand): self
    {
        $this->device = $brand;
        return $this;
    }

    public function getDeviceBrand(): ?string
    {
        return $this->deviceBrand;
    }

    public function setDeviceBrand(?string $brand): self
    {
        $this->deviceBrand = $brand;
        return $this;
    }

    public function getDeviceModel(): ?string
    {
        return $this->deviceModel;
    }

    public function setDeviceModel(?string $model): self
    {
        $this->deviceModel = $model;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ip): self
    {
        $this->ipAddress = $ip;
        return $this;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(Link $link): self
    {
        $this->link = $link;
        return $this;
    }
}
