<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Serializer;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SerializerInterface
{
    /**
     * @param mixed   $data    The data
     * @param string  $format  The format
     * @param Context $context The context
     */
    public function serialize($data, string $format, Context $context): string;
}
