<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action;

/**
 * Interface of single action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface DeleteActionInterface
{
    /**
     * Set if the object deleted must be returned.
     *
     * @param bool $returnedObject Check if the object deleted must be returned
     *
     * @return static
     */
    public function setReturnedObject(bool $returnedObject);

    /**
     * Check if the object deleted must be returned.
     */
    public function hasReturnedObject(): bool;
}
