<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Model;

/**
 * Model for resetting request class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ResettingRequest
{
    protected ?string $username = null;

    /**
     * Set the username.
     *
     * @param null|string $username The username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }
}
