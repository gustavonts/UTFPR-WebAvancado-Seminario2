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
use App\Service\MailService;


final class PedidoController extends AbstractController
{
    #[Route('/pedidos', name:'app_pedidos')]
    public function index(
        Request $request,
        OrderRepository $repository
    ): Response
    {

        $status = $request->query->get('status');
        $cliente = $request->query->get('cliente');
        $data = $request->query->get('data');

        $qb = $repository->createQueryBuilder('p');

        if ($status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($cliente) {
            $qb->andWhere('LOWER(p.customerName) LIKE :cliente')
                ->setParameter('cliente', '%' . strtolower($cliente) . '%');
        }

        if ($data) {
            $inicioDoDia = new \DateTimeImmutable($data . ' 00:00:00');
            $fimDoDia = new \DateTimeImmutable($data . ' 23:59:59');

            $qb->andWhere('p.createdAt >= :inicioDoDia')
                ->andWhere('p.createdAt <= :fimDoDia')
                ->setParameter('inicioDoDia', $inicioDoDia)
                ->setParameter('fimDoDia', $fimDoDia);
        }

        $qb->orderBy('p.createdAt', 'DESC');

        $pedidos = $qb->getQuery()->getResult();


        return $this->render(
            'pedido/index.html.twig',
            [
                'pedidos' => $pedidos,
                'filtroStatus' => $status,
                'filtroCliente' => $cliente,
                'filtroData' => $data
            ]
        );
    }
    
    #[Route('/pedidos/{id}/status', name:'app_pedido_status')]
    public function status(
        Order $pedido,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailService
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

        $destinatario = $pedido->getCustomerEmail() ?? 'noreply@ecommerce.com';

        $mailService->sendStatusUpdate(
            $destinatario,
            $novoStatus,
            $pedido->getNumber()
        );

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
        EntityManagerInterface $entityManager,
        ProdutoRepository $produtoRepository
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

            $itensSelecionados = json_decode($request->request->get('itens', '[]'), true);

            if (is_array($itensSelecionados)) {
                foreach ($itensSelecionados as $itemSelecionado) {
                    $produto = $produtoRepository->find($itemSelecionado['produto'] ?? null);

                    if (!$produto) {
                        continue;
                    }

                    $item = new OrderItem();
                    $item->setOrder($pedido);
                    $item->setProduto($produto);
                    $item->setQuantidade((int) ($itemSelecionado['quantidade'] ?? 1));
                    $item->setPrecoUnitario((string) $produto->getPreco());

                    $entityManager->persist($item);
                }

                $entityManager->flush();
            }

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
                'form' => $form->createView(),
                'produtos' => $produtoRepository->findAll()
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
