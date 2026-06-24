<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Order;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends AbstractController
{

    #[Route('/usuarios', name:'app_usuarios')]
    public function index(
        UsuarioRepository $repository
    ): Response
    {

        $usuarios = $repository->findAll();

        return $this->render(
            'usuario/index.html.twig',
            [
                'usuarios'=>$usuarios
            ]
        );
    }

    #[Route('/usuarios/{id}/aprovar', name:'app_usuario_aprovar')]
    public function aprovar(
        Usuario $usuario,
        EntityManagerInterface $entityManager
    ): Response
    {

        $usuario->setAtivo(true);
        $usuario->setPerfil('ROLE_USER');

        $entityManager->flush();

        return $this->redirectToRoute('app_usuarios');
    }

    #[Route('/pedidos/{id}', name:'app_pedido_show')]
    public function show(
        Order $pedido
    ): Response
    {

        return $this->render(
            'pedido/show.html.twig',
            [
                'pedido'=>$pedido
            ]
        );
    }
}