<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Driver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('lastName')
            ->add('userType', ChoiceType::class, [
                'choices' => [
                    'Passenger' => 'passenger',
                    'Driver' => 'driver',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);

        // L'événement PRE_SET_DATA s'exécute lorsque les données sont injectées dans le formulaire
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData(); // Récupérer l'instance de l'objet (User ou Driver)

                // Ajouter les champs spécifiques aux "drivers" si l'utilisateur est de type "driver"
                if ($data instanceof Driver) {
                    $form->add('adress', TextType::class, [
                        'required' => false,
                    ]);
                    $form->add('dateNaissance', DateType::class, [
                        'widget' => 'single_text',
                        'required' => false,
                    ]);
                    $form->add('photo', TextType::class, [
                        'required' => false,
                    ]);
                    $form->add('numpermis', TextType::class, [
                        'required' => true,
                    ]);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // La classe de base reste User
        ]);
    }
}
