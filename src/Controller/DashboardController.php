<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\UsuarioRepository;
use App\Repository\OrderDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class DashboardController extends AbstractController
{

    #[Route('/', name:'app_dashboard')]
    public function index(
        OrderRepository $orderRepository,
        UsuarioRepository $usuarioRepository,
        OrderDocumentRepository $documentRepository
    ): Response
    {


        $pedidos = $orderRepository->findAll();


        $totalPedidos = count($pedidos);
        $valorTotalPedidos = 0;

        foreach ($pedidos as $pedido) {
            foreach ($pedido->getOrderItems() as $item) {
                $valorTotalPedidos += (float) $item->getPrecoUnitario() * (int) $item->getQuantidade();
            }
        }

        $aguardando = 0;
        $pagos = 0;
        $transporte = 0;
        $entregues = 0;
        $cancelados = 0;

        foreach ($pedidos as $pedido) {

            switch ($pedido->getStatus()) {

                case 'AGUARDANDO_PAGAMENTO':
                    $aguardando++;
                    break;

                case 'PAGO':
                    $pagos++;
                    break;

                case 'EM_TRANSPORTE':
                    $transporte++;
                    break;

                case 'ENTREGUE':
                    $entregues++;
                    break;

                case 'CANCELADO':
                    $cancelados++;
                    break;

            }

        }

        return $this->render(
            'dashboard/index.html.twig',
            [

                'totalPedidos'=>$totalPedidos,
                'valorTotalPedidos' => number_format($valorTotalPedidos, 2, ',', '.'),

                'aguardando'=>$aguardando,

                'pagos'=>$pagos,

                'transporte'=>$transporte,

                'entregues'=>$entregues,

                'cancelados'=>$cancelados,

                'usuarios'=>count(
                    $usuarioRepository->findAll()
                ),

                'documentos'=>count(
                    $documentRepository->findAll()
                )

            ]
        );

    }

}