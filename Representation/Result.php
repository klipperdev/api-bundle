<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Representation;

use Klipper\Component\Model\Traits\IdInterface;
use Klipper\Component\Resource\ResourceInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Result
{
    private string $status;

    /**
     * @var null|int|string
     */
    private $id;

    /**
     * @var null|array|object
     */
    private $data;

    private ?ResultErrors $errors;

    /**
     * @param ResourceInterface $resource The resource
     * @param null|array|object $data     The data
     * @param null|ResultErrors $errors   The result errors
     */
    public function __construct(ResourceInterface $resource, $data, ?ResultErrors $errors)
    {
        $this->status = $resource->getStatus();
        $this->data = $data;
        $this->errors = $errors;

        $realData = $resource->getRealData();

        if ($realData instanceof IdInterface) {
            $this->id = $realData->getId();
        }
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return null|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|array|object
     */
    public function getData()
    {
        return $this->data;
    }

    public function getErrors(): ?ResultErrors
    {
        return $this->errors;
    }
}
