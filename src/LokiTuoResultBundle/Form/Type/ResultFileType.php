<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 13.09.16
 * Time: 10:22.
 */

namespace LokiTuoResultBundle\Form\Type;;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ResultFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label'              => 'form.resultfile.resultfile',
            'translation_domain' => 'LokiTuoResultBundle',
            'attr'               => ['class' => ''],
        ])
            ->add('comment', TextType::class, [
                'label'              => 'form.resultfile.comment',
                'translation_domain' => 'LokiTuoResultBundle',
                'attr'               => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'LokiTuoResultBundle',
                'label'              => 'form.save',
                'attr'               => ['class' => 'btn btn-success'],
            ]);
    }
}
