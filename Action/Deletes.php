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

/**
 * Deletes action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Deletes implements DeleteActionInterface
{
    use DeleteTrait;

    /**
     * @var object[]
     */
    private array $objects;

    private function __construct(array $objects)
    {
        $this->setObjects($objects);
    }

    /**
     * Set the object instances.
     *
     * @param object[] $objects The object instances
     *
     * @return static
     */
    public function setObjects(array $objects): self
    {
        $this->objects = $objects;

        return $this;
    }

    /**
     * Get the object instances.
     *
     * @return object[]
     */
    public function getObjects(): object
    {
        return $this->objects;
    }

    /**
     * Build the action.
     *
     * @param object[] $objects The object instances
     *
     * @return static
     */
    public static function build(array $objects): self
    {
        return new self($objects);
    }
}
