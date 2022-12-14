<?php

namespace App\Form;

use App\Entity\Appointment;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => "Prénom"
            ])
            ->add('lastname', TextType::class, [
                'label' => "Nom"
            ])
            ->add('email', EmailType::class, [
                'label' => "Email"
            ])
            ->add('phone', TelType::class, [
                'label' => "Téléphone"
            ])
            ->add('appointmentDate', DateType::class, [
                'label' => "Date de rendez-vous",
                'attr' => [
                    'min' => (new DateTime())->format('Y-m-d')
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Prendre rendez-vous"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
