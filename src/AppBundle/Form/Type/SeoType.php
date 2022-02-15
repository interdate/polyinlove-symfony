<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('description', TextareaType::class, array(
            'label' => 'Meta Description',
            'required' => false,
        ));

        $builder->add('page', TextType::class, array(
            'label' => 'page',
        ));
    }
    public function getName()
    {
        return 'seo';
    }

}
