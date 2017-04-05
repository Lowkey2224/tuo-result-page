<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 16.09.16
 * Time: 10:12.
 */

namespace LokiTuoResultBundle\Form\Type;;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OwnedCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('card', TextType::class, [
            'label'              => 'form.card.name',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => ['class' => 'form-control'],
        ]);
        $builder->add('level', NumberType::class, [
                'label'              => 'form.card.level',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr'               => ['class' => 'form-control'],
                'required'           => false,
            ]);
        $builder->add('amount', NumberType::class, [
            'data'               => 1,
            'label'              => 'form.card.amount',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => ['class' => 'form-control'], ]);
        $builder->add('submit', SubmitType::class, [
            'label'              => 'form.card.submit',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => ['class' => 'btn btn-success'], ]);
    }
}
