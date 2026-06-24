<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrderRepository;


final class PedidoController extends AbstractController
{
    #[Route('/pedidos', name:'app_pedidos')]
        public function index(
            OrderRepository $repository
        ): Response
        {

            $pedidos = $repository->findAll();


            return $this->render(
                'pedido/index.html.twig',
                [
                    'pedidos'=>$pedidos
                ]
            );
        }
}
