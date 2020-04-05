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
interface PostViewTransformerInterface extends ViewTransformerInterface
{
    /**
     * Action after the transformation.
     *
     * @param array[]|object[] $values The values after transformation
     * @param int              $size   The size of query
     *
     * @return array[]|object[]
     */
    public function postView(array $values, int $size): array;
}
