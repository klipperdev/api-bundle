<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Util;

use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;

/**
 * Util for callable.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class CallableUtil
{
    /**
     * Call the method of the object instance or invoke the callable with the same arguments.
     *
     * @param callable|object $callable  A callable or an object
     * @param string          $method    The method name of the view transformer interface
     * @param array           $arguments The method arguments
     *
     * @return mixed
     */
    public static function call($callable, string $method, array $arguments)
    {
        if (\is_callable($callable)) {
            return $callable(...$arguments);
        }

        if (!\is_object($callable)) {
            throw new InvalidArgumentException('The callable must be a callable or an object');
        }

        if (!method_exists($callable, $method)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The method "%s" does not exist for the class "%s"',
                    $method,
                    \get_class($callable)
                )
            );
        }

        return $callable->{$method}(...$arguments);
    }
}
