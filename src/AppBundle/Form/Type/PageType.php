<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('headerType', ChoiceType::class, array(
            'label' => 'Title type',
            'choices'  => array(
                'H1' => 'h1',
                'H2' => 'h2',
            ),
            'choices_as_values' => true,
        ));

        $builder->add('content', TextareaType::class, array(
            'label' => 'content',
            'required' => false,
        ));



        $builder->add('uri', TextType::class, array(
            'label' => 'URI',
            'required' => false,
        ));

        $builder->add('title', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('description', TextareaType::class, array(
            'label' => 'Meta Description',
            'required' => false,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));

        $builder->add('footerHeader', EntityType::class, array(
            'class' => 'AppBundle:FooterHeader',
            'label' => 'in the footer under the title',
            'choice_label' => 'name',
            'placeholder' => 'choose',
            'multiple' => false,
            'expanded' => false,
            'required' => false,
        ));


    }
    public function getName()
    {
        return 'page';
    }

}
