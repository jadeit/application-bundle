<?php

namespace JadeIT\ApplicationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $edit = $builder->getData()->getId() !== null;
        $builder
            ->add('title')
            ->add('name', null, array('disabled' => $edit))
            ->add('content', 'textarea')
            ->add('active')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'JadeIT\ApplicationBundle\Entity\Content'
            )
        );
    }

    public function getName()
    {
        return 'notes_applicationbundle_contenttype';
    }
}
