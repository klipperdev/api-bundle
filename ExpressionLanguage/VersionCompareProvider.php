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

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Version compare provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class VersionCompareProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('version_compare', static function ($version1, $version2, $operator) {
                return sprintf('version_compare(%1$s, %2$s, %3$s)', $version1, $version2, $operator);
            }, static function (array $values, $input) {
                return $input;
            }),
        ];
    }
}
