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
use Klipper\Component\DoctrineExtensionsExtra\Sortable\RequestSortableQuery;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SortableTransformer implements PrePaginateViewTransformerInterface
{
    protected RequestSortableQuery $helper;

    /**
     * @param RequestSortableQuery $helper The request sortable query helper
     */
    public function __construct(RequestSortableQuery $helper)
    {
        $this->helper = $helper;
    }

    public function prePaginate(Query $query): void
    {
        $this->helper->sort($query);
    }
}
