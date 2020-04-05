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
 * Undeletes action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Undeletes implements DeleteActionInterface
{
    use DeleteTrait;

    /**
     * @var int[]|object[]|string[]
     */
    private $identifiers;

    /**
     * @var null|string
     */
    private $class;

    private function __construct(array $identifiers, ?string $class)
    {
        $this->setClass($class);
        $this->setIdentifiers($identifiers);
    }

    /**
     * Set the resource identifiers.
     *
     * @param int[]|object[]|string[] $identifiers The resource identifiers
     *
     * @return static
     */
    public function setIdentifiers(array $identifiers): self
    {
        $this->identifiers = $identifiers;

        if (null === $this->class && !empty($identifiers) && \is_object($identifiers[0])) {
            $this->class = ClassUtils::getClass($identifiers[0]);
        }

        return $this;
    }

    /**
     * Get the resource identifiers.
     *
     * @return int[]|object[]|string[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * The class name of identifiers (null if identifiers is objects).
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
     * @param int[]|object[]|string[] $identifiers The resource identifiers
     * @param null|string             $class       The class name of identifier (null if identifiers is objects)
     *
     * @return static
     */
    public static function build(array $identifiers, ?string $class = null): self
    {
        return new self($identifiers, $class);
    }
}
