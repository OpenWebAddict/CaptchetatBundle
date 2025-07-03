<?php

namespace OpenWebAddict\CaptchetatBundle\Form\Type;

use OpenWebAddict\CaptchetatBundle\Form\Constraint\CaptchetatValidConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaptchetatFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('captchetatAnswer', TextType::class, [
            'label' => "Réponse utilisateur du Captchetat",
            'help' => "Saisissez le texte affiché par le Captchetat",
            'required' => true,
            'attr' => ['value' => ''],
        ]);

        $builder->add('captchetatUuid', HiddenType::class, [
            'attr' => [
                'value' => null,
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => true,
            'attr' => [
                'class' => 'captcha-widget-container',
            ],
            'constraints' => [
                new CaptchetatValidConstraint(),
            ],
        ])
    }

    public function getBlockPrefix(): string
    {
        return 'captchetat';
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

}
