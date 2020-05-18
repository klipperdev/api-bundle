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
abstract class ViewGroups
{
    /**
     * The default group of api view.
     *
     * @var string
     */
    public const DEFAULT_GROUP = 'Default';

    /**
     * The public group of api view.
     *
     * @var string
     */
    public const PUBLIC_GROUP = 'Public';
}
