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
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableTransformer implements PrePaginateViewTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function prePaginate(Query $query): void
    {
        if (false !== $query->getHint(Query::HINT_CUSTOM_OUTPUT_WALKER)) {
            return;
        }

        QueryUtil::translateQuery($query);
    }
}
