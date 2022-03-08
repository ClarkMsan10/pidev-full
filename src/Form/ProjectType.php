<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Technology;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [ 'required'=> true, 'label'=> 'Titre :', 'attr'=> ['autocomplete'=>'off'] ])
            ->add('summary', TextareaType::class, ['required'=>true, 'label'=>'Résumé :'])
            ->add('description')
            ->add('estimatio', IntegerType::class, ['required' => true, 'label'=> 'Estimation (en semaines)', 'attr'=> ['autocomplete'=>'off']])
            ->add('technologies', EntityType::class, ['class' => Technology::class,
                                                       'choice_label' => 'name',
                                                       'required' => true,
                                                       'label'=> 'Technologies',
                                                       'multiple'=>true,
                                                       'expanded'=>true,
            ])
            ->add('categories', EntityType::class, ['class' => Category::class,
            'choice_label' => 'name',
            'required' => true,
            'label'=> 'Categories',
            'multiple'=>true,
            'expanded'=>true,
])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
