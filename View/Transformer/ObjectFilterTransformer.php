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
    /**
     * @var ObjectFilterInterface
     */
    private $of;

    /**
     * Constructor.
     *
     * @param ObjectFilterInterface $objectFilter The security object filter
     */
    public function __construct(ObjectFilterInterface $objectFilter)
    {
        $this->of = $objectFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function prePaginate(Query $query): void
    {
        $this->of->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function preView(array $results, int $size): array
    {
        $this->of->commit();

        return $results;
    }
}
