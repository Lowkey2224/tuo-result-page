<?php

namespace LokiTuoResultBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label'              => 'form.guild.name',
            'translation_domain' => 'LokiTuoResultBundle',
            'required'           => true,
            'attr'               => [
                'class'       => 'form-control',
            ],
        ])
        ->add('enabled', CheckboxType::class, [
            'label'              => 'form.guild.enabled',
            'translation_domain' => 'LokiTuoResultBundle',
            'required'           => false,
            'attr'               => [
                'class'       => 'form-control',
            ],
        ]);
        $builder->add('submit', SubmitType::class, [
            'label'              => 'form.guild.submit',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr' => ['class' => 'btn btn-success']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LokiTuoResultBundle\Entity\Guild',
        ]);
    }
}
