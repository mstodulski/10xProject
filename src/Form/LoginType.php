<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Throwable;

class LoginType extends AbstractType {

    private AuthenticationUtils $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        try {
            $lastUserName = $this->authenticationUtils->getLastUsername();
        } catch (Throwable) {
            $lastUserName = '';
        }

        $builder
            ->add('username',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'user.user',
                    'data' => $lastUserName,
                    'attr' => [
                        'placeholder' => 'user.user',
                    ]
                ]
            )
            ->add('password',
                PasswordType::class,
                [
                    'required' => true,
                    'label' => 'user.password',
                    'attr' => [
                        'placeholder' => 'user.password',
                    ]
                ]
            )
            ->add('targetPath',
                HiddenType::class,
                [
                    'data' => '/'
                ]
            )
            ->add('save',
                SubmitType::class,
                [
                    'label' => 'user.log_in'
                ]
            )
        ;
    }

    public function getBlockPrefix() : string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ));
    }
}
