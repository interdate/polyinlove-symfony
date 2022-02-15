<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reportEmail', TextType::class, array(
            'label' => 'mail to send reports to',
        ));

        $builder->add('contactEmail', TextType::class, array(
            'label' => 'mail to send contact us forms to',
        ));

        $builder->add('deleteMessagesAfterDaysNumber', TextType::class, array(
            'label' => 'delete messages after x days. if left empty, messages will not be deleted',
            'required' => false,
        ));

        $builder->add('sendMessageUsersNumber', TextType::class, array(
            'label' => 'maximum amount of users a member may contact a day. if left empty, no limit will be imposed',
            'required' => false,
        ));

        $builder->add('sendMessageUsersNumberWithoutPhoto', TextType::class, array(
            'label' => 'maximum amount of users a member without a photo may contact a day. if left empty, no limit will be imposed',
            'required' => false,
        ));

        $builder->add('usersPerPage', TextType::class, array(
            'label' => 'users per page',
        ));

        $builder->add('isCharge', CheckboxType::class, array(
            'label' => 'is the site in payment mode',
            'required' => false,
        ));

        $builder->add('messagePopularityDaysNumber', TextType::class, array(
            'label' => 'oldest message counted when calculating popularity',
        ));

        $builder->add('userConsideredAsOnlineAfterLastActivityMinutesNumber', TextType::class, array(
            'label' => 'how many minutes after using site is member considered online',
        ));

        $builder->add('userConsideredAsNewAfterDaysNumber', TextType::class, array(
            'label' => 'how many days is a user considered new',
        ));


        $builder->add('smsUsername', TextType::class, array(
           'label' => 'username '
        ));

        $builder->add('smsPassword', TextType::class, array(
           'label' => 'password'
        ));

        $builder->add('smsSufix', TextType::class, array(
           'label' => 'sender'
        ));

    }
    public function getName()
    {
        return 'settings';
    }

}
