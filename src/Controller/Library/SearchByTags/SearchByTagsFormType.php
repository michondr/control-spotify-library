<?php

declare(strict_types = 1);

namespace App\Controller\Library\SearchByTags;

use App\Entity\Tag\Tag;
use App\Entity\Tag\TagFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchByTagsFormType extends AbstractType
{
    public function __construct(
        private TagFacade $tagFacade
    )
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $builder
            ->add('tags', ChoiceType::class, [
                'label' => false,
                'choices' => $this->tagFacade->getUserTags(),
                'choice_label' => fn(Tag $tag) => $tag->getName(),
                'choice_value' => fn(Tag $tag) => $tag->getId(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('search', SubmitType::class, ['label' => 'Search']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchByTagsFormRequest::class,
        ]);
    }
}