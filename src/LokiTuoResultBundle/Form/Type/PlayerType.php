<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.11.16
 * Time: 17:28.
 */

namespace LokiTuoResultBundle\Form\Type;;

use LokiUserBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['guilds'] = $this->transformGuilds($options['guilds']);

        $builder
            ->add('name', TextType::class, [
                'label'              => 'form.player.playername',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr'               => ['class' => 'form-control'], ])
            ->add('guild', EntityType::class, [
                'class'              => 'LokiTuoResultBundle\Entity\Guild',
                'label'              => 'form.player.guild',
                'translation_domain' => 'LokiTuoResultBundle',
                'multiple'           => false,
                'attr'               => [
                    'class' => 'form-control',
                ],
            ])
            ->add('owner', EntityType::class, [
                'class'              => 'LokiUserBundle\Entity\User',
                'required'           => false,
                'label'              => 'form.player.owner',
                'translation_domain' => 'LokiTuoResultBundle',
                'choice_label'       => 'username',
                'multiple'           => false,
                'query_builder'      => function (UserRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.enabled = true')
                        ->orderBy('u.username', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr'  => [
                    'class' => 'btn btn-success',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LokiTuoResultBundle\Entity\Player',
            'guilds'     => [
                'Please Select your Guild...' => null,
            ],
        ]);
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
