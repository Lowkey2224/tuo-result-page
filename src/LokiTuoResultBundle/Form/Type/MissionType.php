<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 14.12.16
 * Time: 23:25.
 */

namespace LokiTuoResultBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label'              => 'form.mission.name',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => [
                'class' => 'form-control',
            ], ])
            ->add('type', TextType::class, [
            'label'              => 'form.mission.type',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => [
                'class' => 'form-control',
            ], ]);
        $builder->add('submit', SubmitType::class, ['label' => 'Save', 'attr' => ['class' => 'btn btn-success']]);
    }
}
