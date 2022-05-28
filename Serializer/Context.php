<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Serializer;

use Klipper\Bundle\ApiBundle\ViewGroups;

/**
 * Stores the serialization or deserialization context (groups, version, ...).
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Context
{
    private array $attributes = [];

    private ?string $version = null;

    private ?array $groups = null;

    private ?bool $maxDepthChecks = null;

    private ?bool $serializeNull = null;

    public function __construct()
    {
        $this->addGroup(ViewGroups::DEFAULT_GROUP);
    }

    /**
     * Sets an attribute.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Checks if contains a normalization attribute.
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Gets an attribute.
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Gets the attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Sets the normalization version.
     *
     * @return static
     */
    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets the normalization version.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Adds a normalization group.
     *
     * @return static
     */
    public function addGroup(string $group): self
    {
        if (null === $this->groups) {
            $this->groups = [];
        }
        if (!\in_array($group, $this->groups, true)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * Adds normalization groups.
     *
     * @param string[] $groups
     *
     * @return static
     */
    public function addGroups(array $groups): self
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Gets the normalization groups.
     *
     * @return null|string[]
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * Set the normalization groups.
     *
     * @param null|string[] $groups
     *
     * @return static
     */
    public function setGroups(?array $groups = null): self
    {
        $this->groups = $groups;

        return $this;
    }

    public function enableMaxDepthChecks(): self
    {
        $this->maxDepthChecks = true;

        return $this;
    }

    public function disableMaxDepthChecks(): self
    {
        $this->maxDepthChecks = false;

        return $this;
    }

    public function hasMaxDepthChecks(): ?bool
    {
        return $this->maxDepthChecks;
    }

    /**
     * Sets serialize null.
     *
     * @return static
     */
    public function setSerializeNull(?bool $serializeNull): self
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    /**
     * Gets serialize null.
     */
    public function getSerializeNull(): ?bool
    {
        return $this->serializeNull;
    }
}
