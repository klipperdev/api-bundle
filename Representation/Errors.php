<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Representation;

use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Errors
{
    /**
     * @var null|string[]
     */
    protected $errors;

    /**
     * @var null|Errors[]
     */
    protected $children;

    /**
     * Get the error messages.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Get the the map of the children errors.
     *
     * @return Errors[]
     */
    public function getChildren(): array
    {
        return $this->children ?? [];
    }

    /**
     * Add the error message.
     *
     * @param string $message The error message
     *
     * @return static
     */
    public function addError(string $message): self
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * Add the child error.
     *
     * @param string $name       The child name
     * @param Errors $childError The child error
     *
     * @return static
     */
    public function addChild(string $name, Errors $childError): self
    {
        $this->children[$name] = $childError;

        return $this;
    }

    /**
     * Check if the child error exists.
     *
     * @param string $name The child name
     */
    public function hasChild(string $name): bool
    {
        return isset($this->children[$name]);
    }

    /**
     * Get the child error.
     *
     * @param string $name The child name
     */
    public function getChild(string $name): Errors
    {
        if (!isset($this->children[$name])) {
            throw new InvalidArgumentException(sprintf('The "%s" child does not exists', $name));
        }

        return $this->children[$name];
    }
}
