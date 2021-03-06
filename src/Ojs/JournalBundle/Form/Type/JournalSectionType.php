<?php

namespace Ojs\JournalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JournalSectionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$journal = $options['journal'];
        //$user = $options['user'];
        $builder
            ->add('translations', 'a2lix_translations_gedmo',[
                'translatable_class' => 'Ojs\JournalBundle\Entity\JournalSection'
            ])
            ->add(
                'allowIndex',
                'checkbox',
                array(
                    'label' => 'journalsection.hide_title'
                )
            )
            ->add(
                'hideTitle',
                'checkbox',
                array(
                    'label' => 'journalsection.allow_index'
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Ojs\JournalBundle\Entity\JournalSection',
                'user' => null,
                'journal' => null,
                'attr' => [
                    'novalidate' => 'novalidate',
                    'class' => 'form-validate',
                ],
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ojs_journalbundle_journalsection';
    }
}
