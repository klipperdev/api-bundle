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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Api version provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ApiVersionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var null|Request
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack The request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('api_version', static function ($version, $operator = null) {
                $operator = $operator ?? 'null';
                $str = '\Klipper\Bundle\ApiBundle\Util\VersionUtil::compare($request->get(\'version\'), %1$s, %2$s)';

                return sprintf($str, $version, $operator);
            }, static function (array $values, $input) {
                return $input;
            }),
        ];
    }
}
