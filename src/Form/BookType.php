<?php
// src/Form/BookType.php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // Import ChoiceType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [ // Changed to ChoiceType
                'choices' => [
                    'Fiction' => 'fiction',
                    'Non-Fiction' => 'non_fiction',
                    'Science Fiction' => 'science_fiction',
                    'Fantasy' => 'fantasy',
                    'Mystery' => 'mystery',
                    'Biography' => 'biography',
                    'History' => 'history',
                    'Self-Help' => 'self_help',
                ],
                'placeholder' => 'Select a category', // Optional placeholder
                'required' => true,
            ])
            ->add('author'); // Add Author selection if needed
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
