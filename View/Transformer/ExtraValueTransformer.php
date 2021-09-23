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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExtraValueTransformer implements GetViewTransformerInterface
{
    public const VALUE_PREFIX = '__';

    public const PROPERTY_PREFIX = '@';

    public function getView($value)
    {
        if (\is_array($value) && $this->hasExtraValue($value)) {
            $tmpValue = $value;
            $extraValues = [];

            foreach ($tmpValue as $key => $val) {
                if (0 === strpos($key, static::VALUE_PREFIX)) {
                    $extraValues[$key] = $val;
                    unset($tmpValue[$key]);
                }
            }

            $data = current($tmpValue);
            array_pop($tmpValue);

            if (\is_object($data)) {
                foreach ($extraValues as $key => $val) {
                    $dynProperty = static::PROPERTY_PREFIX.substr($key, \strlen(static::VALUE_PREFIX));
                    $data->{$dynProperty} = $val;
                }

                return $data;
            }
            if (\is_array($data)) {
                foreach ($extraValues as $key => $val) {
                    $data[static::PROPERTY_PREFIX.substr($key, \strlen(static::VALUE_PREFIX))] = $val;
                }

                return $data;
            }
        }

        return $value;
    }

    private function hasExtraValue(array $value): bool
    {
        foreach ($value as $key => $va) {
            if (0 === strpos($key, '__')) {
                return true;
            }
        }

        return false;
    }
}
