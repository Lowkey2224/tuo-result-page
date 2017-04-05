<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.10.16
 * Time: 16:49.
 */

namespace LokiTuoResultBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class MassOwnedCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cards', TextareaType::class, [
            'label'              => 'form.card.mass.cards',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => [
                'class'              => 'form-control',
                'placeholder'        => 'form.card.mass.placeholder',
                'translation_domain' => 'LokiTuoResultBundle',
            ],
        ]);
        $builder->add('submit', SubmitType::class, [
            'label'              => 'form.card.mass.submit',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => ['class' => 'btn btn-success'],
        ]);
    }
}
