<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
 

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('picture', FileType::class, [
                'required'      => false, 
                'data_class'    => null
            ])
            ->add('title', TextType::class, ['required'=> false])
            ->add('content', CKEditorType::class, ['required'=> false])
            #->add('publicationDate')
            #->add('lastUpdateDate')
            ->add('isPublised', CheckboxType::class, ['required'=> false])
            ->add('categories', EntityType::class, [
                'class'         => Category::class,
                'choice_label'  => 'libele',
                'multiple'      => true,
                'required'      => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
