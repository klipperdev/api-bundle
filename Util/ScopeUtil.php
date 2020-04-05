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
abstract class ScopeUtil
{
    /**
     * Get the role name form the oauth scope.
     *
     * @param string $scope The oauth scope
     */
    public static function getRole(string $scope): string
    {
        return 'ROLE_SCOPE_'.strtoupper(str_replace(':', '_', $scope));
    }

    /**
     * Check if the oauth scope is defined in the list of roles.
     *
     * @param string   $scope The oauth scope
     * @param string[] $roles The role names
     */
    public static function has(string $scope, array $roles): bool
    {
        return \in_array(static::getRole($scope), $roles, true);
    }

    /**
     * Check if the one of the oauth scopes is defined in the list of roles.
     *
     * @param string[] $scopes The oauth scopes
     * @param string[] $roles  The role names
     */
    public static function hasAny(array $scopes, array $roles): bool
    {
        foreach ($scopes as $scope) {
            if (static::has($scope, $roles)) {
                return true;
            }
        }

        return false;
    }
}
