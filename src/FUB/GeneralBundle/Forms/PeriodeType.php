<?php
/**
 * Created by IntelliJ IDEA.
 * User: eddy
 * Date: 28/05/2017
 * Time: 20:02
 */


namespace FUB\GeneralBundle\Forms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder
            ->add('type', ChoiceType::class, array(
                'choices'  => array(
                    'Journalier' => 'daily',
                    'Hebdomadaire' => 'weekly',
                    'Mensuel' => 'monthly',
                ),
            ))
            ->add('debut', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('fin', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('valider', SubmitType::class);
        */
        $builder
            ->add('debut', TextType::class)
            ->add('fin', TextType::class)
            ->add('valider', SubmitType::class);

        /*$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $product = $event->getData();
            $form = $event->getForm();
            $type=$form['type']->getData();

            echo '<script>alert("event! '.$product->getDebut().'");</script>';
        });*/
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert',
            'allow_extra_fields' => true
        ));
    }
}
