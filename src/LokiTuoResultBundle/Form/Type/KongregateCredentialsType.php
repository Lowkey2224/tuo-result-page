<?php

namespace LokiTuoResultBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KongregateCredentialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('kongUserName', TextType::class, [
                'label' => 'form.player.kongusername',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('kongPassword', TextType::class, [
                'label' => 'form.player.konguserpassword',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('kongId', NumberType::class, [
                'label' => 'form.player.kongId',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('synCode', TextType::class, [
                'label' => 'form.player.synCode',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('kongToken', TextType::class, [
                'label' => 'form.player.kongToken',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('tuUserId', NumberType::class, [
                'label' => 'form.player.tuUserId',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LokiTuoResultBundle\Entity\KongregateCredentials'
        ]);
    }
}
