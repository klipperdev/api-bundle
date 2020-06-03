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

use Klipper\Component\Resource\ResourceListInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ResultList
{
    private string $status;

    private bool $hasErrors;

    /**
     * @var null|Result[]
     */
    private ?array $records = null;

    public function __construct(ResourceListInterface $resourceList)
    {
        $this->status = $resourceList->getStatus();
        $this->hasErrors = $resourceList->hasErrors();
    }

    /**
     * Get the status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Check if the result has an error.
     */
    public function isHasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * Get the records.
     *
     * @return Result[]
     */
    public function getRecords(): array
    {
        return $this->records ?? [];
    }

    /**
     * Add the record.
     */
    public function addRecord(Result $record): self
    {
        $this->records[] = $record;

        return $this;
    }
}
