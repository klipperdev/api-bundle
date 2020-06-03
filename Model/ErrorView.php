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

use Symfony\Component\ErrorHandler\Exception\FlattenException;

/**
 * Model for error view.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ErrorView
{
    public int $code;

    public string $message;

    /**
     * @var null|FlattenException|\Throwable
     */
    public $exception;

    /**
     * @param int             $code      The status code
     * @param string          $message   The message
     * @param null|\Throwable $throwable The exception
     */
    public function __construct(int $code, string $message, ?\Throwable $throwable = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->exception = $throwable && class_exists(FlattenException::class)
            ? FlattenException::createFromThrowable($throwable)
            : $throwable;
    }
}
