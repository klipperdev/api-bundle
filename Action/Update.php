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
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ActionWithObjectTrait;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ProcessFormInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ProcessFormTrait;
use Klipper\Component\Resource\Handler\FormConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Update action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Update extends FormConfig implements ActionInterface, ProcessFormInterface
{
    use ActionWithObjectTrait;
    use ProcessFormTrait;

    private function __construct(string $formType, ?object $object)
    {
        parent::__construct($formType);

        $this->setObject($object);
        $this->setMethod(Request::METHOD_PATCH);
        $this->addBuilderHandler(new ListenerFormBuilderHandler($this));
    }
}
