<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:41.
 */

namespace App\LokiTuoResultBundle\Form\Type;

;

use App\LokiTuoResultBundle\Entity\BattleGroundEffect;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Repository\BattleGroundEffectRepository;
use App\LokiTuoResultBundle\Repository\GuildRepository;
use App\LokiTuoResultBundle\Repository\PlayerRepository;
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
        $builder
            ->add('missions', TextareaType::class, [
                'label' => 'form.simulation.missions',
                'attr'  => [
                    'class' => 'form-control',
                ],
            ])
            ->add('backgroundeffect', EntityType::class, [
                'class' => 'App\LokiTuoResultBundle\Entity\BattleGroundEffect',
                'label'        => 'form.simulation.backgroundEffect',
                'required'     => false,
                'choice_label' => function (BattleGroundEffect $bge) {
                    return $bge->getName() . ' (' . $bge->getDescription() . ')';
                },
                'multiple'      => false,
                'query_builder' => function (BattleGroundEffectRepository $br) {
                    return $br->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('structures', TextType::class, [
                'label'    => 'form.simulation.structures',
                'required' => false,
                'attr'     => [
                    'class' => 'form-control',
                ],
            ])
            ->add('enemyStructures', TextType::class, [
                'label'    => 'form.simulation.enemystructures',
                'required' => false,
                'attr'     => [
                    'class' => 'form-control',
                ],
            ])
            ->add('iterations', NumberType::class, [
                'label' => 'form.simulation.iterations',
                'attr'  => [
                    'class' => 'form-control',
                ],
            ])
            ->add('threadCount', NumberType::class, [
                'label' => 'form.simulation.threads',
                'attr'  => [
                    'class' => 'form-control',
                ],
            ])
            ->add('guild', EntityType::class, [
                'label'         => 'form.simulation.guild',
                'class' => 'App\LokiTuoResultBundle\Entity\Guild',
                'required'         => false,
                'query_builder' => function (GuildRepository $gr) {
                    $qb = $gr->createQueryBuilder('g')
                        ->where('g.enabled = 1')
                        ->orderBy('g.name', 'ASC');

                    return $qb;
                },
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('players', EntityType::class, [
                // query choices from this entity
                'class' => 'LokiTuoResultBundle:Player',
                'label' => 'form.simulation.players',

                // use the User.username property as the visible option string
                'choice_label' => 'name',
                'choice_attr'  => function (Player $val) {
                    return ['data-guild' => $val->getGuild()];
                },

                // used to render a select box, check boxes or radios
                'multiple'      => true,
                'query_builder' => function (PlayerRepository $pr) {
                    $qb = $pr->createQueryBuilder('p')
                        ->where('p.active = ?1')
                        ->setParameter(1, true)//not empty
                        ->orderBy('p.name', 'ASC');

                    return $qb;
                },
                'attr' => [
                    'class' => 'form-control',
                ],
                // 'expanded' => true,
            ])
            ->add('simType', ChoiceType::class, [
                'label'   => 'form.simulation.type',
                'choices' => [
                    'climb' => 'climb',
                    'raid'  => 'raid climb',
//                    'No Guild' => null,
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('scriptType', ChoiceType::class, [
                'label'   => 'form.simulation.scripttype',
                'choices' => [
                    'Shell Script'           => 'shell',
                    'Windows command Script' => 'command',
//                    'No Guild' => null,
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('ordered', CheckboxType::class, [
                'label'    => 'form.simulation.ordered',
                'required' => false,
                'attr'     => [
//                    'class' => 'form-control'
                ],
            ])
            ->add('surge', CheckboxType::class, [
                'label'    => 'form.simulation.surge',
                'required' => false,
                'attr'     => [
//                    'class' => 'form-control'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.simulation.generate',
                'attr'  => [
                    'size'  => 20,
                    'class' => 'btn btn-info form-control',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\LokiTuoResultBundle\Service\Simulation\Simulation',
        ]);
    }
}
