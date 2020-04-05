<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Metadata\Guess;

use Klipper\Component\Metadata\ActionMetadataBuilderInterface;
use Klipper\Component\Metadata\Guess\GuessActionConfigInterface;
use Klipper\Component\Metadata\MetadataContexts;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuessActionOrganizationRequirements implements GuessActionConfigInterface
{
    /**
     * @var string
     */
    private $userContextPattern;

    /**
     * @var string
     */
    private $orgContextPattern;

    /**
     * Constructor.
     *
     * @param string $userContextPattern The route requirements pattern of organization parameter for user available context
     * @param string $orgContextPattern  The route requirements pattern of organization parameter for org available context
     */
    public function __construct(
        string $userContextPattern = 'user',
        string $orgContextPattern = '[a-zA-Z0-9\.\-\_]+'
    ) {
        $this->userContextPattern = $userContextPattern;
        $this->orgContextPattern = $orgContextPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function guessActionConfig(ActionMetadataBuilderInterface $builder): void
    {
        $req = $builder->getRequirements();

        if (!isset($req['organization'])
                && false !== strpos((string) $builder->getPath(), '/{organization}/')) {
            $ac = $builder->getParent()->getAvailableContexts() ?? [];
            $value = [];

            if (\in_array(MetadataContexts::USER, $ac, true)) {
                $value[] = $this->userContextPattern;
            }

            if (\in_array(MetadataContexts::ORGANIZATION, $ac, true)) {
                $value[] = $this->orgContextPattern;
            }

            if (!empty($value)) {
                $builder->addRequirements([
                    'organization' => implode('|', $value),
                ]);
            }
        }
    }
}
