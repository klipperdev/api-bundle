<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action\Traits;

use Klipper\Bundle\ApiBundle\Controller\Action\DeleteActionInterface;

/**
 * Trait for the new options.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait DeleteTrait
{
    private bool $returnedObject = false;

    /**
     * @see DeleteActionInterface::setReturnedObject()
     */
    public function setReturnedObject(bool $returnedObject): self
    {
        $this->returnedObject = $returnedObject;

        return $this;
    }

    /**
     * @see DeleteActionInterface::hasReturnedObject()
     */
    public function hasReturnedObject(): bool
    {
        return $this->returnedObject;
    }
}
