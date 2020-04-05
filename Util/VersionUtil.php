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

/**
 * Util for api version.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class VersionUtil
{
    /**
     * Compare the request version with the required version.
     *
     * @param string      $requestVersion The version in request
     * @param string      $version        The required version
     * @param null|string $operator       The operator
     */
    public static function compare(?string $requestVersion, string $version, ?string $operator = null): bool
    {
        $operator = $operator ?? '>=';
        $requestVersion = static::getRequestVersion($requestVersion);

        return (bool) version_compare($requestVersion, $version, $operator);
    }

    /**
     * Get the api version.
     *
     * @param null|string $version The request version
     */
    protected static function getRequestVersion(?string $version): string
    {
        $version = $version ?? '0';

        if (false !== $pos = strrpos($version, '-')) {
            $version = substr($version, 0, $pos);
        }

        return $version;
    }
}
