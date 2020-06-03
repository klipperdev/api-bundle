<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Action;

use Klipper\Bundle\ApiBundle\Controller\Action\DeleteActionInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\DeleteTrait;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * Undelete action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Undelete implements DeleteActionInterface
{
    use DeleteTrait;

    /**
     * @var int|object|string
     */
    private $identifier;

    private ?string $class;

    private function __construct($identifier, ?string $class)
    {
        $this->setClass($class);
        $this->setIdentifier($identifier);
    }

    /**
     * Set the resource identifier.
     *
     * @param int|object|string $identifier The resource identifier
     *
     * @return static
     */
    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        if (null === $this->class && \is_object($identifier)) {
            $this->class = ClassUtils::getClass($identifier);
        }

        return $this;
    }

    /**
     * Get the resource identifier.
     *
     * @return int|object|string
     */
    public function getIdentifier(): object
    {
        return $this->identifier;
    }

    /**
     * The class name of identifier (null if identifier is an object).
     *
     * @param null|string $class The object classname
     *
     * @return static
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the class name of identifier.
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Build the action.
     *
     * @param int|object|string $identifier The resource identifier
     * @param null|string       $class      The class name of identifier (null if identifier is an object)
     *
     * @return static
     */
    public static function build($identifier, ?string $class = null): self
    {
        return new self($identifier, $class);
    }
}
