<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('store', ChoiceType::class, [
                'label' => 'Magasin',
                'choices' => [
                    'Auchan' => 'auchan',
                    'Carrefour' => 'carrefour',
                    'Super U' => 'super_u',
                ],
            ])
            ->add('storeId', IntegerType::class, [
                'label' => 'Id Magasin',
            ])
            ->add('storeName', TextType::class, [
                'label' => 'Nom du Magasin',
                'required' => false
            ])
            ->add('onBreak', CheckboxType::class, [
                'label' => 'Mettre en pause',
            ])
        ;
    }
}