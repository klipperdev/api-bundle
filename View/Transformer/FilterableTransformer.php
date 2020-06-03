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
use Klipper\Component\DoctrineExtensionsExtra\Filterable\RequestFilterableQuery;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableTransformer implements PrePaginateViewTransformerInterface
{
    protected RequestFilterableQuery $helper;

    /**
     * @param RequestFilterableQuery $helper The request filterable query helper
     */
    public function __construct(RequestFilterableQuery $helper)
    {
        $this->helper = $helper;
    }

    public function prePaginate(Query $query): void
    {
        $this->helper->filter($query);
    }
}
