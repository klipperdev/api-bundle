<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Model;

/**
 * Model for choice view.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ChoiceView
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var int|string
     */
    public $value;

    public string $label;

    /**
     * @param int|string $id    The id
     * @param int|string $value The view representation of the choice
     * @param string     $label The label displayed to humans
     */
    public function __construct($id, $value, string $label)
    {
        $this->id = $id;
        $this->value = $value;
        $this->label = $label;
    }
}
