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

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Model for check authorization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CheckAuthorization
{
    /**
     * @var string
     *
     * @Assert\Expression(
     *     expression="null !== this.getToken()",
     *     message="oauth_access_token.token.not_blank"
     * )
     */
    protected $token;

    /**
     * Set the token.
     *
     * @param null|string $token The token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * Get the token.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }
}
