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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcessCommandFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Infinite\ImportBundle\Entity\Import $import */
        $import = $options['import'];
        $firstRow = $import->getFirstLine();

        $choices = array();
        foreach ($firstRow->getData() as $column => $value) {
            $choices[$column] = sprintf('%s — %s', $column, $value);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($choices) {
            /** @var \Infinite\ImportBundle\Processor\ProcessCommandField $data */
            $data = $event->getData();
            $form = $event->getForm();

            $options = array(
                'choices' => $choices,
                'required' => $data->required,
            );
            $options['empty_value'] = 'Not provided';

            $form->add('populateWith', 'choice', $options);
        });

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Infinite\ImportBundle\Processor\ProcessCommandField'
        ));

        $resolver->setRequired(array(
            'import'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'infinite_import_process_field';
    }
}
