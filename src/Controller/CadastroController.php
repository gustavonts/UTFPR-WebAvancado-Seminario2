<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CadastroController extends AbstractController
{
    #[Route('/cadastro', name: 'app_cadastro')]
    public function cadastro(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        if ($request->isMethod('POST')) {

            $usuario = new Usuario();

            $usuario->setNome(
                $request->request->get('nome')
            );

            $usuario->setEmail(
                $request->request->get('email')
            );

            $usuario->setSenha(
                $passwordHasher->hashPassword(
                    $usuario,
                    $request->request->get('senha')
                )
            );

            $usuario->setAtivo(false);
            $usuario->setPerfil('ROLE_PENDING');

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Cadastro realizado com sucesso. Aguarde aprovação do administrador.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('cadastro/index.html.twig');
    }
}