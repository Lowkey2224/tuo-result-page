<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:41
 */

namespace LokiTuoResultBundle\Form;

use LokiTuoResultBundle\Entity\BattleGroundEffect;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Repository\BattleGroundEffectRepository;
use LokiTuoResultBundle\Repository\PlayerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

        $options['guilds'] = $this->transformGuilds($options['guilds']);


        $builder
            ->add('missions', TextareaType::class, [
                'label' => "form.simulation.missions",
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('backgroundeffect', EntityType::class, [
                'class' => 'LokiTuoResultBundle\Entity\BattleGroundEffect',
                'label' => "form.simulation.backgroundEffect",
                'required' => false,
                'choice_label' => function (BattleGroundEffect $bge) {
                    return $bge->getName() . " (" . $bge->getDescription() . ")";
                },
                'multiple' => false,
                'query_builder' => function (BattleGroundEffectRepository $br) {
                    return $br->createQueryBuilder('p')
                        ->orderBy('p.category', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('structures', TextType::class, [
                'label' => "form.simulation.structures",
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('enemyStructures', TextType::class, [
                'label' => "form.simulation.enemystructures",
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('iterations', NumberType::class, [
                'label' => 'form.simulation.iterations',
                'attr' => [
                    'class' => 'form-control'
                ]])
            ->add('threadCount', NumberType::class, [
                'label' => 'form.simulation.threads',
                'attr' => [
                    'class' => 'form-control'
                ]])
            ->add('guild', ChoiceType::class, array(
                'label' => "form.simulation.guild",
                'choices' => $options['guilds']
            , 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('players', EntityType::class, array(
                // query choices from this entity
                'class' => 'LokiTuoResultBundle:Player',
                'label' => "form.simulation.players",

                // use the User.username property as the visible option string
                'choice_label' => 'name',
                'choice_attr' => function (Player $val) {
                    return ['data-guild' => $val->getCurrentGuild()];
                },

                // used to render a select box, check boxes or radios
                'multiple' => true,
                'query_builder' => function (PlayerRepository $pr) {
                    $qb = $pr->createQueryBuilder('p')
                        ->where('p.currentGuild != ?1')
                        ->andWhere('p.active = ?2')
                        ->setParameter(1, "")//not empty
                        ->setParameter(2, true)//not empty
                        ->orderBy('p.name', 'ASC');
                    return $qb;
                },
                'attr' => [
                    'class' => 'form-control',
                ]
                // 'expanded' => true,
            ))
            ->add('simType', ChoiceType::class, array(
                'label' => "form.simulation.type",
                'choices' => [
                    'climb' => "climb",
                    'raid' => "raid climb",
//                    'No Guild' => null,
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('scriptType', ChoiceType::class, array(
                'label' => "form.simulation.scripttype",
                'choices' => [
                    'Shell Script' => "shell",
                    'Windows command Script' => "command",
//                    'No Guild' => null,
                ], 'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('ordered', CheckboxType::class, array(
                'label' => "form.simulation.ordered",
                'required' => false,
                'attr' => [
//                    'class' => 'form-control'
                ]
            ))
            ->add('surge', CheckboxType::class, array(
                'label' => "form.simulation.surge",
                'required' => false,
                'attr' => [
//                    'class' => 'form-control'
                ]
            ))
            ->add('save', SubmitType::class, [
                'label' => "form.simulation.generate",
                'attr' => [
                    'size' => 20,
                    'class' => 'btn btn-info form-control'
                ]
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LokiTuoResultBundle\Service\Simulation\Simulation',
            'guilds' => [
                'Please Select your Guild...' => null,
            ],
        ));
    }

    private function transformGuilds(array $guilds)
    {
        $return = ['Please Select your Guild...' => null];
        foreach ($guilds as $guild) {
            $return[$guild] = $guild;
        }
        return $return;
    }
}
