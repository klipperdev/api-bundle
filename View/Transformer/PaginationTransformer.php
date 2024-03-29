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
use Klipper\Component\DoctrineExtensionsExtra\Pagination\RequestPaginationQuery;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PaginationTransformer implements PrePaginateViewTransformerInterface
{
    protected RequestPaginationQuery $helper;

    protected bool $fetchJoinCollection;

    /**
     * @param RequestPaginationQuery $helper The request pagination query helper
     */
    public function __construct(RequestPaginationQuery $helper, bool $fetchJoinCollection = false)
    {
        $this->helper = $helper;
        $this->fetchJoinCollection = $fetchJoinCollection;
    }

    public function prePaginate(Query $query): void
    {
        $this->helper->paginate($query, $this->fetchJoinCollection);
    }
}
