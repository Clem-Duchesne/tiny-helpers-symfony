<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Votre identifiant'])
            ->add('github', UrlType::class, ['label' => 'Votre lien github'])
            ->add('password', PasswordType::class, ['label' => 'Votre mot de passe'])
            ->add('email', EmailType::class, ['label' => 'Votre email'])
            ->add('file', FileType::class, ['label' => 'Votre image', 'label_attr' => ['class' => 'file__label'], 'attr' => ['id'=>'file', 'class' => 'file']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
