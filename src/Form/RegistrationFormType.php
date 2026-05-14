<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom *',
                'attr' => ['placeholder' => 'Votre prénom'],
                'constraints' => [new NotBlank(message: 'Le prénom est obligatoire.')],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom *',
                'attr' => ['placeholder' => 'Votre nom'],
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('gsm', TextType::class, [
                'label' => 'Numéro de téléphone *',
                'attr' => ['placeholder' => '06 12 34 56 78'],
                'constraints' => [new NotBlank(message: 'Le numéro de téléphone est obligatoire.')],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse *',
                'attr' => ['placeholder' => '12 rue de la Paix'],
                'constraints' => [new NotBlank(message: 'L\'adresse est obligatoire.')],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville *',
                'attr' => ['placeholder' => 'Bordeaux'],
                'constraints' => [new NotBlank(message: 'La ville est obligatoire.')],
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code postal *',
                'attr' => ['placeholder' => '33000'],
                'constraints' => [new NotBlank(message: 'Le code postal est obligatoire.')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail *',
                'attr' => ['placeholder' => 'votre@email.fr'],
                'constraints' => [new NotBlank(message: 'L\'email est obligatoire.')],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe *',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Minimum 10 caractères',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le mot de passe est obligatoire.'),
                    new Length(
                        min: 10,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        max: 4096,
                    ),
                    new Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/',
                        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions générales d\'utilisation',
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions générales.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
