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

use Doctrine\ORM\Query;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PrePaginateViewTransformerInterface extends ViewTransformerInterface
{
    /**
     * Action before the query pagination and transformation.
     *
     * @param Query $query The doctrine orm query
     */
    public function prePaginate(Query $query): void;
}
