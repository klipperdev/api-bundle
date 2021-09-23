<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Serializer\Listener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractJmsFilterSubscriber implements EventSubscriberInterface
{
    protected MetadataManagerInterface $metadataManager;

    protected RequestStack $requestStack;

    protected ?array $cacheFields = null;

    public function __construct(
        MetadataManagerInterface $metadataManager,
        RequestStack $requestStack
    ) {
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Get the request query search.
     */
    protected function getFields(): array
    {
        if (null === $this->cacheFields) {
            $this->cacheFields = [];
            $requestFields = $this->getRequestFields();

            if (!empty($requestFields)) {
                $fields = array_map('trim', explode(',', $requestFields));

                foreach ($fields as $field) {
                    $exp = array_map('trim', explode('.', $field));

                    if (2 === \count($exp)) {
                        $this->cacheFields[$exp[0]][$exp[1]] = true;
                    } else {
                        $this->cacheFields['_global'][$exp[0]] = true;
                    }
                }
            }
        }

        return $this->cacheFields;
    }

    /**
     * Get the fields config in request.
     */
    protected function getRequestFields(): string
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-fields')) {
                return (string) $request->headers->get('x-fields', '');
            }

            return (string) $request->query->get('fields', '');
        }

        return '';
    }
}
