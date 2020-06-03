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
use Klipper\Component\Security\ObjectFilter\ObjectFilterInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectFilterTransformer implements PrePaginateViewTransformerInterface, PreViewTransformerInterface
{
    private ObjectFilterInterface $of;

    /**
     * @param ObjectFilterInterface $objectFilter The security object filter
     */
    public function __construct(ObjectFilterInterface $objectFilter)
    {
        $this->of = $objectFilter;
    }

    public function prePaginate(Query $query): void
    {
        $this->of->beginTransaction();
    }

    public function preView(array $results, int $size): array
    {
        $this->of->commit();

        return $results;
    }
}
