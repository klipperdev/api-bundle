<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\View\Transformer;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface GetViewTransformerInterface extends ViewTransformerInterface
{
    /**
     * Get the view of value.
     *
     * @param array|object $value The value
     *
     * @return array|object
     */
    public function getView($value);
}
