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
use Klipper\Component\DoctrineExtensionsExtra\Searchable\RequestSearchableQuery;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SearchableTransformer implements PrePaginateViewTransformerInterface
{
    /**
     * @var RequestSearchableQuery
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param RequestSearchableQuery $helper The request filterable query helper
     */
    public function __construct(RequestSearchableQuery $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function prePaginate(Query $query): void
    {
        $this->helper->filter($query);
    }
}
