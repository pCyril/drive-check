<?php
namespace App\Form\Type;

use App\Entity\Store;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductAlertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('store', EntityType::class, [
                'label' => 'Magasin',
                'class' => 'App:Store',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('s');
                    return $qb
                        ->andWhere('s.store = \'auchan\'')
                        ->orderBy('s.storeName', 'ASC');
                },
                'choice_label' => function (?Store $store) {
                    return $store->getStoreName()?:$store->getStore() . ' (' . $store->getStoreId() . ')';
                }
            ])
            ->add('product_url', TextType::class, [
                'mapped' => false,
                'label' => 'Url du produit',
                'attr' => [
                    'placeholder' => 'https://www.auchandrive.fr/catalog/francine-farine-de-ble-1kg-P68456',
                    'title' => 'form.order.id.title',
                ],
            ])
        ;
    }
}