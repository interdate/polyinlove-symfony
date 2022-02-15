<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SlideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('headerType', ChoiceType::class, array(
            'label' => 'Title type',
            'choices'  => array(
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
                'h4' => 'h4',
                'h5' => 'h5',
            ),
            'choices_as_values' => true,
        ));

        $builder->add('content', TextareaType::class, array(
            'label' => 'content',
        ));

        $builder->add('imageName', HiddenType::class, array(
            'label' => 'image',
            'required' => false,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));


    }
    public function getName()
    {
        return 'slide';
    }

}
