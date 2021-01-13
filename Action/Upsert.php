<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Action;

use Klipper\Bundle\ApiBundle\Controller\Action\ActionInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\ListenerFormBuilderHandler;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ActionWithClassOrObjectTrait;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\NewOptionsInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\NewOptionsTrait;
use Klipper\Component\Resource\Handler\FormConfig;

/**
 * Upsert action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Upsert extends FormConfig implements ActionInterface, NewOptionsInterface
{
    use ActionWithClassOrObjectTrait;
    use NewOptionsTrait;

    private function __construct(string $formType, $object)
    {
        parent::__construct($formType);

        $this->setObject($object);
        $this->addBuilderHandler(new ListenerFormBuilderHandler($this));
    }
}
