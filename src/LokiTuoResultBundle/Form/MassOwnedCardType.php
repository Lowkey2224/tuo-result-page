<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.10.16
 * Time: 16:49
 */

namespace LokiTuoResultBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class MassOwnedCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cards', TextareaType::class, ['label' => 'Cards', 'attr' => ['class' => 'form-control', 'placeholder'=> 'Enter your Cards here. 1 Card per Line']]);
        $builder->add('submit', SubmitType::class, ['label' => 'Add Card', 'attr' => ['class' => 'btn btn-success']]);
    }

}