<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:41
 */

namespace LokiTuoResultBundle\Form;

use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Repository\PlayerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimulationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('missions', TextareaType::class, [
                'label' => "Missions (comma-separated)",
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('backgroundeffect', TextType::class, [
                'label' => "Background Effect (leave empty if none)",
                'required' => false,
                'attr' => [
                'class' => 'form-control'
                ]
            ])
            ->add('structures', TextType::class, [
                'label' => "Structures (comma-separated)",
                'required' => false,
                'attr' => [
                'class' => 'form-control'
                ]
            ])
            ->add('iterations', NumberType::class, [
                'label' => '# of Iterations',
                'attr' => [
                'class' => 'form-control'
                ]])
            ->add('guild', ChoiceType::class, array(
                'label' => "Guild",
                'choices' => [
                    'Please Select your Guild...' => null,
                    'CNS' => "CNS",
                    'CTP' => "CTP",
                    'CTN' => "CTN",
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('players', EntityType::class, array(
                // query choices from this entity
                'class' => 'LokiTuoResultBundle:Player',
                'label' => "Players to Sim",

                // use the User.username property as the visible option string
                'choice_label' => 'name',
                'choice_attr' => function (Player $val) {
                    return ['data-guild' => $val->getCurrentGuild()];
                },

                // used to render a select box, check boxes or radios
                'multiple' => true,
                'query_builder' => function (PlayerRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->where('p.currentGuild != :empty')->setParameter('empty', "") //not empty
                        ->orderBy('p.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control',
                ]
                // 'expanded' => true,
            ))
            ->add('simType', ChoiceType::class, array(
                'label' => "Simulation Type",
                'choices' => [
                    'climb' => "climb",
                    'raid' => "raid",
//                    'No Guild' => null,
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('scriptType', ChoiceType::class, array(
                'label' => "Script Type",
                'choices' => [
                    'Shell Script' => "shell",
                    'Windows command Script' => "command",
//                    'No Guild' => null,
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('save', SubmitType::class, [
                'label' => "Generate Script",
                'attr' => [
                    'size' => 20,
                    'class' => 'btn btn-info form-control'
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LokiTuoResultBundle\Service\Simulation\Simulation',
        ));
    }
}
