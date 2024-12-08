<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', TextType::class, [
                'label' => 'Enter your postal code',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Postal Code',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Postal code cannot be empty.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Postal code must be exactly 5 digits.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
