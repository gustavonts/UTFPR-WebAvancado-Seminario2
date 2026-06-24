<?php

namespace App\Controller;
use App\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\OrderStatusHistory;
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
    
    #[Route('/pedidos/{id}/status', name:'app_pedido_status')]
    public function status(
        Order $pedido,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $novoStatus = $request->request->get('status');


        $historico = new OrderStatusHistory();

        $historico->setOrder($pedido);

        $historico->setOldStatus(
            $pedido->getStatus()
        );


        $historico->setNewStatus(
            $novoStatus
        );


        $historico->setChangedAt(
            new \DateTimeImmutable()
        );


        $historico->setChangedBy(
            $this->getUser()
        );


        $pedido->setStatus(
            $novoStatus
        );


        $entityManager->persist($historico);

        $entityManager->flush();


        return $this->redirectToRoute(
            'app_pedido_show',
            [
                'id'=>$pedido->getId()
            ]
        );
    }
}
