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
interface PreViewTransformerInterface extends ViewTransformerInterface
{
    /**
     * Action before the transformation.
     *
     * @param array[]|object[] $values The values before transformation
     * @param int              $size   The size of query
     *
     * @return array[]|object[]
     */
    public function preView(array $values, int $size): array;
}
