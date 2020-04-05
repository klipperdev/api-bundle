<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Representation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ResultErrors extends Errors
{
    /**
     * @var string
     */
    private $message;

    /**
     * Constructor.
     *
     * @param string $message The error message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Get the error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
