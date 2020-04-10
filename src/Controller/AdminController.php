<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(UserRepository $repo)
    {
        $users = $repo->findAll();
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/editRole/{id}",name="editRole")
     */
    public function editRole(User $user = null)
    {
        if ($user == null) {
            $this->addFlash('danger', 'Utilisateur introuvable');
            return $this->redirectToRoute('home');
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            $user->setRoles(['ROLE_USER']);
        } else {
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        }


        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

        $this->addFlash("success", "Rôle modifié");
        return $this->redirectToRoute('admin');
    }
}
