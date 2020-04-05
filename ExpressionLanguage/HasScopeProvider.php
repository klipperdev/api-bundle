<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\ExpressionLanguage;

use Klipper\Bundle\ApiBundle\Util\ScopeUtil;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Has scope provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HasScopeProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('has_scope', static function ($scope, $orBasicAuth = true) {
                $exp = sprintf('\Klipper\Bundle\ApiBundle\Util\ScopeUtil::has(%s, $roles)', $scope);

                if ($orBasicAuth) {
                    $class = '\\'.UsernamePasswordToken::class;
                    $exp2 = sprintf('$token && $token instanceof %1$s && !$trust_resolver->isAnonymous($token)', $class);
                    $exp = $exp.' || ('.$exp2.')';
                }

                return $exp;
            }, static function (array $variables, $scope, $orBasicAuth = true) {
                return ScopeUtil::has($scope, $variables['roles'])
                    || ($orBasicAuth
                        && isset($variables['token'])
                        && $variables['token'] instanceof UsernamePasswordToken
                        && !$variables['trust_resolver']->isAnonymous($variables['token']));
            }),

            new ExpressionFunction('has_any_scope', static function ($scopes, $orBasicAuth = true) {
                $scopesStr = 'array("'.implode('", "', (array) $scopes).'")';
                $exp = sprintf('\Klipper\Bundle\ApiBundle\Util\ScopeUtil::hasAny(%s, $roles)', $scopesStr);

                if ($orBasicAuth) {
                    $class = '\\'.UsernamePasswordToken::class;
                    $exp2 = sprintf('$token && $token instanceof %1$s && !$trust_resolver->isAnonymous($token)', $class);
                    $exp = $exp.' || ('.$exp2.')';
                }

                return $exp;
            }, static function (array $variables, $scopes, $orBasicAuth = true) {
                return ScopeUtil::hasAny((array) $scopes, $variables['roles'])
                    || ($orBasicAuth
                        && isset($variables['token'])
                        && $variables['token'] instanceof UsernamePasswordToken
                        && !$variables['trust_resolver']->isAnonymous($variables['token']));
            }),
        ];
    }
}
