<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action;

use Klipper\Component\Resource\Handler\FormConfigInterface;

/**
 * Interface of single action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ActionInterface extends FormConfigInterface, CommonActionInterface
{
}
