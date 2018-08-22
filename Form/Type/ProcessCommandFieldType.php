<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Form\Type;

use Infinite\ImportBundle\Entity\Import;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Infinite\ImportBundle\Processor\ProcessCommandField;

class ProcessCommandFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Import $import */
        $import = $options['import'];
        $firstRow = $import->getFirstLine();

        $choices = array();
        foreach ($firstRow->getData() as $column => $value) {
            $choices[sprintf('%s — %s', $column, $value)] = $column;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($choices) {
            /** @var ProcessCommandField $data */
            $data = $event->getData();
            $form = $event->getForm();

            $options = array(
                'choices' => $choices,
                'required' => $data->required,
                'choices_as_values' => true,
            );
            $options['placeholder'] = 'Not provided';

            $form->add('populateWith', ChoiceType::class, $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProcessCommandField::class
        ));

        $resolver->setRequired(array(
            'import'
        ));
    }
}
