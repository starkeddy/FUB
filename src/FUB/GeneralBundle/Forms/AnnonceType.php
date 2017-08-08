<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/19/17
 * Time: 8:40 AM
 */

namespace FUB\GeneralBundle\Forms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class AnnonceType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('upd_dt', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            ))
            ->add('contenu', TextareaType::class)
            ->add('create',SubmitType::class);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert',
            'allow_extra_fields' => true
        ));
    }
}