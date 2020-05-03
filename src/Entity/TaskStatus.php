<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 */
class TaskStatus
{
    public const PENDING = 'PENDING';
    public const CANCELLED = 'CANCELLED';
    public const FAILED = 'FAILED';
    public const COMPLETED = 'COMPLETED';
    public const EXPIRED = 'EXPIRED';

    public const ALL_STATUSES = [
        self::PENDING,
        self::CANCELLED,
        self::FAILED,
        self::COMPLETED,
        self::EXPIRED,
    ];

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $handle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     *
     * @JMS\Type("string")
     * @JMS\Groups({"taskList", "taskDetails"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @param string $handle
     * @param string $description
     */
    public function __construct(string $handle, string $description)
    {
        $this->handle = $handle;
        $this->description = $description;
        $this->created = new \DateTime();
        $this->active = true;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return TaskStatus
     */
    public function enable(): self
    {
        $this->active = true;

        return $this;
    }

    /**
     * @return TaskStatus
     */
    public function disable(): self
    {
        $this->active = false;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDescription();
    }
}
