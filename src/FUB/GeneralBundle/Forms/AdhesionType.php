<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/1/17
 * Time: 10:56 PM
 */

namespace FUB\GeneralBundle\Forms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdhesionType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('isMorale',CheckboxType::class, array('required' => false))
            ->add('picture',FileType::class)
            ->add('nom',TextType::class)
            ->add('prenom', TextType::class)
            ->add('dateNaissance', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('perenom',TextType::class)
            ->add('merenom', TextType::class)
            ->add('nationalite', TextType::class)
            ->add('profession', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('country', TextType::class)
            ->add('contact', TextType::class)
            ->add('mail', TextType::class)
            ->add('montantChiffre', TextType::class)
            ->add('montantLettre', TextType::class)
            ->add('dateContrat', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('dureeContrat', ChoiceType::class, array(
                'choices'  => array(
                    '12 mois' => 12,
                    '24 mois' => 24,
                    '36 mois' => 36,
                ),
            ))
            ->add('heritieNomPrenom', TextType::class)
            ->add('heritierContact', TextType::class)
            ->add('heritierMail', TextType::class)
            ->add('heritierAdresse', TextType::class)
            ->add('sondageFub', TextareaType::class)
            ->add('motivationFub', TextareaType::class)
            ->add('valid', SubmitType::class)
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert',
            'allow_extra_fields' => true
        ));
    }

    /*public function getBlockPrefix()
    {
        return 'oc_platformbundle_adverttype';
    }*/
}