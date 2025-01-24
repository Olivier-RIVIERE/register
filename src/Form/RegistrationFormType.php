<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use App\Entity\User;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'required' => true
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Votre mot de passe',
                'required' => true
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmer votre mot de passe',
                'required' => true
            ])
            ->add('username', TextType::class, [
                'label' => 'Votre pseudo',
                'required' => true
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Votre avatar',
                'required' => false,
                'mapped' => false
            ])
        ;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if ($password !== $confirmPassword) {
                $form->get('confirm_password')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
