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

/**
 * Interface of action with creation options.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NewOptionsInterface
{
    /**
     * Set the new options for the creation of the instance.
     *
     * @param array $options The new options
     *
     * @return static
     */
    public function setNewOptions(array $options);

    /**
     * Get the new options for the creation of the instance.
     */
    public function getNewOptions(): array;
}
