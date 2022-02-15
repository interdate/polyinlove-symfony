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


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('locale', ChoiceType::class, array(
            'label' => 'Language',
            'choices'  => array(
                'he' => 'he',
                'en' => 'en',
                'ru' => 'ru',
            ),
            'choices_as_values' => true,
        ));

        $builder->add('headerType', ChoiceType::class, array(
            'label' => 'Title type',
            'choices'  => array(
                'H1' => 'h1',
                'H2' => 'h2',
            ),
            'choices_as_values' => true,
        ));


        $builder->add('brief', TextareaType::class, array(
            'label' => 'brief',
        ));

        $builder->add('content', TextareaType::class, array(
            'label' => 'content',
            'required' => false,
        ));

        $builder->add('imageName', HiddenType::class, array(
            'label' => 'image',
            'required' => false,
        ));

        $builder->add('imageAlt', TextType::class, array(
            'label' => 'Alt',
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

        $builder->add('isOnHomepage', CheckboxType::class, array(
            'label' => 'appears on home page',
            'required' => false,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'is active',
            'required' => false,
        ));

    }
    public function getName()
    {
        return 'article';
    }

}
