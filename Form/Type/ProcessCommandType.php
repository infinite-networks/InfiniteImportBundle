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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Infinite\ImportBundle\Processor\ProcessCommand;

class ProcessCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ProcessCommand $command */
            $command = $event->getData();
            $form = $event->getForm();

            $form->add('fields', CollectionType::class, array(
                'entry_type' => ProcessCommandFieldType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'prototype' => false,
                'entry_options' => array(
                    'import' => $command->import,
                )
            ));
        });

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProcessCommand::class
        ));
    }
}
