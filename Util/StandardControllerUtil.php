<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Util;

use Doctrine\Persistence\ObjectRepository;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class StandardControllerUtil
{
    /**
     * Find the create query builder method for list.
     */
    public static function findDefaultCreateQueryBuilderMethod(ObjectRepository $repository): string
    {
        if (method_exists($repository, 'createTranslatedQueryBuilderForList')) {
            return 'createTranslatedQueryBuilderForList';
        }

        if (method_exists($repository, 'createQueryBuilderForList')) {
            return 'createQueryBuilderForList';
        }

        if (method_exists($repository, 'createTranslatedQueryBuilder')) {
            return 'createTranslatedQueryBuilder';
        }

        return 'createQueryBuilder';
    }
}
