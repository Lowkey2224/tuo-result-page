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

        $options['guilds'] = $this->transformGuilds($options['guilds']);

        $builder
            ->add('name', TextType::class, ['label' => 'form.player.playername',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr' => ['class' => 'form-control']])
            ->add('currentGuild', ChoiceType::class, array(
                'label' => "form.player.guild",
                'translation_domain' => 'LokiTuoResultBundle',
                'choices' => $options['guilds'],
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LokiTuoResultBundle\Entity\Player',
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
