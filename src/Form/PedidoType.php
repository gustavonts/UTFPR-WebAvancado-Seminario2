<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PedidoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName')
            ->add('customerEmail')
            ->add('totalAmount')
            ->add('status', ChoiceType::class, [
                'choices' => [

                    'Aguardando pagamento' => 'AGUARDANDO_PAGAMENTO',

                    'Pago' => 'PAGO',

                    'Em transporte' => 'EM_TRANSPORTE',

                    'Entregue' => 'ENTREGUE',

                    'Cancelado' => 'CANCELADO',

                ],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}