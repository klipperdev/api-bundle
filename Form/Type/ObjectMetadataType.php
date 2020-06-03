<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Form\Type;

use Klipper\Component\DoctrineExtensionsExtra\Form\Type\TranslatableType;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Object Metadata Form Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectMetadataType extends AbstractType
{
    private MetadataManagerInterface $metadataManager;

    public function __construct(MetadataManagerInterface $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $meta = $this->metadataManager->get($options['data_class']);

        foreach ($meta->getFields() as $fieldMeta) {
            if ($fieldMeta->isPublic() && !$fieldMeta->isReadOnly()) {
                $name = $this->convertName($fieldMeta->getField());
                $builder->add($name, $fieldMeta->getFormType(), array_merge($fieldMeta->getFormOptions(), [
                    'property_path' => $fieldMeta->getField(),
                    'required' => $fieldMeta->isRequired(),
                    'validation_groups' => $fieldMeta->getGroups(),
                ]));
            }
        }

        foreach ($meta->getAssociations() as $assoMeta) {
            if ($assoMeta->isPublic() && !$assoMeta->isReadOnly()) {
                $name = $this->convertName($assoMeta->getAssociation());
                $builder->add($name, $assoMeta->getFormType(), array_merge($assoMeta->getFormOptions(), [
                    'property_path' => $assoMeta->getAssociation(),
                    'required' => $assoMeta->isRequired(),
                    'validation_groups' => $assoMeta->getGroups(),
                ]));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (Options $options, $value = null): ?array {
                $hasMeta = null === $value && isset($options['data_class'])
                    && $this->metadataManager->has($options['data_class']);

                return $hasMeta
                    ? $this->metadataManager->get($options['data_class'])->getGroups()
                    : null;
            },
        ]);
    }

    public function getParent(): ?string
    {
        return class_exists(TranslatableType::class) ? TranslatableType::class : parent::getParent();
    }

    /**
     * Convert the camel case name into underscore name.
     *
     * @param string $name The name
     */
    private function convertName(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }
}
