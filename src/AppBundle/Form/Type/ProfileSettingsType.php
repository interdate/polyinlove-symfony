<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('isSentEmail', CheckboxType::class, array(
    			'label' => 'Email Notification', //'התראה על הודעה למייל',
    			'required' => false,
    	));
    	
    	$builder->add('isSentPush', CheckboxType::class, array(
    			'label' => 'Push notification', //'התראה על הודעה לטלפון (Push)',
    			'required' => false,
    	));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
        ));
    }

    public function getName()
    {
        return 'profileSettings';
    }

}
