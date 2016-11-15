<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.11.16
 * Time: 17:28
 */

namespace LokiTuoResultBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['label' => 'Playername', 'attr' => ['class' => 'form-control']])

            ->add('currentGuild', ChoiceType::class, array(
                'label' => "Guild",
                'choices' => [
                    'CNS' => "CNS",
                    'CTP' => "CTP",
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('submit', SubmitType::class);
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LokiTuoResultBundle\Entity\Player',
        ));
    }
}
