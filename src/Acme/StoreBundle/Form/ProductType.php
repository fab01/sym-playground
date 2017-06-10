<?php

namespace Acme\StoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductType extends AbstractType
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
            ->add('price', TextType::class, array(
                    'error_bubbling' => false,
                    'attr' => array(
                        'placeholder' => 'Currency format 10.00',
                        'class' => 'form-control'
                    )
            ))
            ->add('description', TextareaType::class, array(
                    'attr' => array(
                        'class' => 'form-control'
                    )
            ))
            ->add('category', EntityType::class, array(
                    'class' => 'AcmeStoreBundle:Category',
                    'attr' => array(
                        'class' => 'form-control'
                    )      
            ))
            ->add('save', SubmitType::class, array(
                    'label' => 'Crea prodotto',
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
            'data_class' => 'Acme\StoreBundle\Entity\Product',
            'error_mapping' => array(
                'priceLegal' => 'price'
                )
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        //return 'acme_storebundle_product';
        return 'product';
    }

}
