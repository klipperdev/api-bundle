<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class RequestHeaders
{
    /**
     * The resource force delete.
     *
     * @var string
     */
    public const FORCE_DELETE = 'X-Force-Delete';

    /**
     * The resource transactional.
     *
     * @var string
     */
    public const TRANSACTIONAL = 'X-Transactional';
}
