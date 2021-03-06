<?php

namespace Ojs\AdminBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JournalApplicationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'journal.title', 'attr' => ['class' => 'validate[required]']])
            ->add('titleAbbr', null, ['label' => 'journal.titleAbbr', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'titleTransliterated',
                null,
                ['label' => 'journal.titleTransliterated', 'attr' => ['class' => 'validate[required]']]
            )
            ->add('subtitle', null, ['label' => 'journal.subtitle', 'attr' => ['class' => 'validate[required]']])
            ->add('domain', null, ['label' => 'journal.domain', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'country',
                'entity',
                array(
                    'class' => 'OjsLocationBundle:Country',
                    'label' => 'journal.country',
                    'attr' => ['class' => 'select2-element validate[required]'],
                )
            )
            ->add('issn', null, ['label' => 'journal.issn', 'attr' => ['class' => 'validate[required] maskissn']])
            ->add('eissn', null, ['label' => 'journal.eissn', 'attr' => ['class' => 'validate[required] maskissn']])
            ->add(
                'firstPublishDate',
                'collot_datetime',
                [
                    'label' => 'journal.firstPublishDate',
                    'attr' => ['class' => 'validate[required]'],
                    'date_format' => 'dd-MM-yyyy',
                    'pickerOptions' => [
                        'format' => 'dd-mm-yyyy',
                        'startView' => 'month',
                        'minView' => 'month',
                        'todayBtn' => 'true',
                        'todayHighlight' => 'true',
                        'autoclose' => 'true',
                    ],
                ]
            )
            ->add('period', null, ['label' => 'journal.period', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'tags',
                null,
                ['attr' => ['class' => 'tags form-control validate[required]', 'label' => 'journal.tags']]
            )
            ->add('url', null, ['label' => 'journal.url', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'institution',
                'entity',
                array(
                    'class' => 'OjsJournalBundle:Institution',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                                    ->andWhere('i.verified = :verified')
                                    ->setParameter('verified', true);
                    },
                    'attr' => ['class' => 'select2-element validate[required]'],
                    'label' => 'journal.institution',
                )
            )
            ->add(
                'languages',
                'entity',
                array(
                    'class' => 'OjsJournalBundle:Lang',
                    'multiple' => true,
                    'label' => 'journal.languages',
                    'attr' => ['class' => 'select2-element validate[required]'],
                )
            )
            ->add(
                'subjects',
                'entity',
                array(
                    'class' => 'OjsJournalBundle:Subject',
                    'multiple' => true,
                    'label' => 'journal.subjects',
                    'attr' => ['class' => 'select2-element validate[required]'],
                )
            )
            ->add('image', 'hidden')
            ->add('header', 'hidden')
            ->add('address', null, ['label' => 'journal.address', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'phone',
                null,
                ['label' => 'journal.phone', 'attr' => ['class' => 'validate[required,custom[email]]']]
            )
            ->add(
                'email',
                'email',
                ['label' => 'journal.email', 'attr' => ['class' => 'validate[required,custom[email]]']]
            )
            ->add('editorName', null, ['mapped' => false, 'label' => 'journal.editor_name', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'editorSurname',
                null,
                ['mapped' => false, 'label' => 'journal.editor_surname', 'attr' => ['class' => 'validate[required]']]
            )
            ->add('editorPhone', null, ['mapped' => false, 'label' => 'journal.editor_phone', 'attr' => ['class' => 'validate[required]']])
            ->add(
                'editorEmail',
                'email',
                ['mapped' => false, 'label' => 'journal.editor_email', 'attr' => ['class' => 'validate[required,custom[email]]']]
            )
            ->add(
                'assistantEditorName',
                null,
                ['mapped' => false, 'label' => 'journal.assistant_editor_name', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'assistantEditorSurname',
                null,
                ['mapped' => false, 'label' => 'journal.assistant_editor_surname', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'assistantEditorPhone',
                null,
                ['mapped' => false, 'label' => 'journal.assistant_editor_phone', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'assistantEditorEmail',
                null,
                ['mapped' => false, 'label' => 'journal.assistant_editor_email', 'attr' => ['class' => 'validate[required,custom[email]]']]
            )
            ->add(
                'techContactName',
                null,
                ['mapped' => false, 'label' => 'journal.tech_contact_name', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'techContactSurname',
                null,
                ['mapped' => false, 'label' => 'journal.tech_contact_surname', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'techContactPhone',
                null,
                ['mapped' => false, 'label' => 'journal.tech_contact_phone', 'attr' => ['class' => 'validate[required]']]
            )
            ->add(
                'techContactEmail',
                null,
                ['mapped' => false, 'label' => 'journal.tech_contact_email', 'attr' => ['class' => 'validate[required,custom[email]]']]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Ojs\JournalBundle\Entity\Journal',
                'attr' => ['class' => 'form-validate'],
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ojs_journalbundle_journalapplication';
    }
}
