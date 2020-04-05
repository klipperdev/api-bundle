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
 * Delete action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Delete implements DeleteActionInterface
{
    use DeleteTrait;

    /**
     * @var null|object
     */
    private $object;

    private function __construct(?object $object)
    {
        $this->setObject($object);
    }

    /**
     * Set the object instance.
     *
     * @param null|object $object The object instance
     *
     * @return static
     */
    public function setObject(?object $object): self
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get the object instance.
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * Build the action.
     *
     * @param null|object $object The object instance
     *
     * @return static
     */
    public static function build(?object $object): self
    {
        return new self($object);
    }
}
