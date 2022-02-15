<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class ContactType extends AbstractType
{
    public $disableToken = true;

    public function disableToken  ()
    {
        $this->disableToken = false;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => $this->disableToken,
            'translation_domain' => 'forms',
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, array(
            'label' => '* Email',
        ));

        $builder->add('subject', TextType::class, array(
            'label' => '* Subject',
            'mapped' => false,
        ));

        $builder->add('text', TextareaType::class, array(
            'label' => '* Message',
            'mapped' => false,
        ));
    }

    public function getName()
    {
        return 'contact';
    }

}
