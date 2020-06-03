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
 * Trait for the new options.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait NewOptionsTrait
{
    private array $newOptions = [];

    /**
     * @see NewOptionsInterface::setNewOptions()
     */
    public function setNewOptions(array $options): self
    {
        $this->newOptions = $options;

        return $this;
    }

    /**
     * @see NewOptionsInterface::getNewOptions()
     */
    public function getNewOptions(): array
    {
        return $this->newOptions;
    }
}
