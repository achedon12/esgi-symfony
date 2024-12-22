<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UpdateUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('image', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Birthdate',
            ])
            ->add('bio', TextType::class, [
                'label' => 'Bio',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Other' => 'other'
                ],
                'label' => 'Gender',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}