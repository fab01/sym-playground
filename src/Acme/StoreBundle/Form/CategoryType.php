<?php

namespace Acme\StoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                    'attr' => array(
                        'class' => 'form-control'
                    )
            ))
            ->add('save', SubmitType::class, array(
                    'label' => 'Crea categoria',
                    'attr' => array(
                        'class' => 'btn btn-lg btn-success'
                    )
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\StoreBundle\Entity\Category'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'category';
    }

}
