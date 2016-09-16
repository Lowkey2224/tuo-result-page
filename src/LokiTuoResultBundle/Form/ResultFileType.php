<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 13.09.16
 * Time: 10:22
 */

namespace LokiTuoResultBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ResultFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, ['label' => 'Resultfile', 'attr' => ['class' => '']])
            ->add('submit', SubmitType::class);
    }

}