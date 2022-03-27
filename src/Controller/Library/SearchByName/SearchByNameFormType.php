<?php

declare(strict_types = 1);

namespace App\Controller\Library\SearchByName;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchByNameFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $builder
            ->add('query', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('search', SubmitType::class, ['label' => 'Search']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchByNameFormRequest::class,
        ]);
    }
}