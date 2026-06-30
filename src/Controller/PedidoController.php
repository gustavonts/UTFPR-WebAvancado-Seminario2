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
use App\Repository\ProdutoRepository;
use App\Form\PedidoType;
use App\Entity\OrderDocument;
use App\Entity\OrderItem;
use App\Form\OrderItemType;


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
        
        if ($novoStatus === $pedido->getStatus()) {

            $this->addFlash(
                'warning',
                'O pedido já está com esse status.'
            );

            return $this->redirectToRoute(
                'app_pedido_show',
                [
                    'id' => $pedido->getId()
                ]
            );

        }

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

    #[Route('/pedidos/novo', name: 'app_pedido_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $pedido = new Order();


        $form = $this->createForm(
            PedidoType::class,
            $pedido
        );


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $pedido->setCreatedAt(
                new \DateTimeImmutable()
            );

            $ultimoPedido = $entityManager
                ->getRepository(Order::class)
                ->findOneBy([], ['id' => 'DESC']);


            if ($ultimoPedido) {

                $proximoNumero = (int) $ultimoPedido->getNumber() + 1;

            } else {

                $proximoNumero = 1;

            }

            $pedido->setNumber(
                str_pad($proximoNumero, 6, '0', STR_PAD_LEFT)
            );


            $entityManager->persist($pedido);

            $entityManager->flush();


            return $this->redirectToRoute(
                'app_pedido_show',
                [
                    'id' => $pedido->getId()
                ]
            );
        }


        return $this->render(
            'pedido/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    #[Route('/pedidos/excluir/{id}', name: 'app_pedido_delete')]
    public function delete(
        Order $pedido,
        EntityManagerInterface $entityManager
    ): Response
    {
        $entityManager->remove($pedido);
        $entityManager->flush();

        return $this->redirectToRoute('app_pedidos');
    }

    #[Route('/pedidos/{id}/documento', name: 'app_pedido_documento', methods: ['POST'])]
    public function uploadDocumento(
        Order $pedido,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $arquivo = $request->files->get('documento');



        if (!$arquivo) {
            return $this->redirectToRoute(
                'app_pedido_show',
                ['id'=>$pedido->getId()]
            );
        }

        if (!$arquivo->isValid()) {

            dd($arquivo->getErrorMessage());

        }

        if ($arquivo) {


           $nomeArquivo = uniqid()
                .'.'
                .$arquivo->getClientOriginalExtension();


            $mimetype = $arquivo->getMimeType();

            $nomeOriginal = $arquivo->getClientOriginalName();


            $destino = $this->getParameter('kernel.project_dir')
                . '/public/uploads';


            if (!is_dir($destino)) {
                mkdir($destino, 0777, true);
            }


            $arquivo->move(
                $destino,
                $nomeArquivo
            );


            $documento = new OrderDocument();

            $documento->setOriginalName(
                $nomeOriginal
            );

            $documento->setFilePath(
                $nomeArquivo
            );


            $documento->setMimeType(
                $mimetype
            );


            $documento->setOrder(
                $pedido
            );


            $entityManager->persist($documento);

            $entityManager->flush();

        }


        return $this->redirectToRoute(
            'app_pedido_show',
            [
                'id' => $pedido->getId()
            ]
        );
    }

    #[Route('/pedidos/{id}', name:'app_pedido_show')]
    public function show(
        Order $pedido,
        ProdutoRepository $produtoRepository
    ): Response
    {

        return $this->render(
            'pedido/show.html.twig',
            [
                'pedido' => $pedido,
                'produtos' => $produtoRepository->findAll()
            ]
        );
    }

    #[Route('/pedidos/{id}/produto/add', name: 'app_pedido_produto_add', methods:['POST'])]
    public function adicionarProduto(
        Order $pedido,
        Request $request,
        EntityManagerInterface $entityManager,
        ProdutoRepository $produtoRepository
    ): Response
    {
        $produto = $produtoRepository->find(
            $request->request->get('produto')
        );

        $item = new OrderItem();

        $item->setOrder($pedido);

        $item->setProduto($produto);

        $item->setQuantidade(
            $request->request->getInt('quantidade')
        );

        $item->setPrecoUnitario(
            $produto->getPreco()
        );

        $entityManager->persist($item);

        $entityManager->flush();

        return $this->redirectToRoute(
            'app_pedido_show',
            [
                'id'=>$pedido->getId()
            ]
        );
    }
}
