<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Version\Resolver;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HeaderVersionResolver implements VersionResolverInterface
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * Constructor.
     *
     * @param string $headerName The header name
     */
    public function __construct(string $headerName)
    {
        $this->headerName = $headerName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        if (!$request->headers->has($this->headerName)) {
            return false;
        }

        $header = $request->headers->get($this->headerName);

        return is_scalar($header) ? $header : (string) $header;
    }
}
