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
    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    /**
     * @var null|\Exception
     */
    public $exception;

    /**
     * Constructor.
     *
     * @param int             $code      The status code
     * @param string          $message   The message
     * @param null|\Exception $exception The exception
     */
    public function __construct(int $code, string $message, ?\Exception $exception = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->exception = $exception && class_exists(FlattenException::class)
            ? FlattenException::create($exception)
            : $exception;
    }
}
