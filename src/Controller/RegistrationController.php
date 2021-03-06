<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserEditType;
use App\Security\AppAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $fichier = $form->get('photo')->getData();
            //Si un fichier a été uploadé
            if ($fichier) {
                // On renomme le fichier
                $nomFicher = uniqid() . '.' . $fichier->guessExtension();
                try {
                    // on essaie de deplacer le fichier
                    $fichier->move(
                        $this->getParameter('upload_dir'),
                        $nomFicher
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', "Impossible d'uploader le fichier");
                    return $this->redirectToRoute('user');
                }
                $user->setPhoto($nomFicher);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}",name="user_edit")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        $fichier = $form->get('photo')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($fichier) {
                // On renomme le fichier
                $nomFicher = uniqid() . '.' . $fichier->guessExtension();
                try {
                    // on essaie de deplacer le fichier
                    $fichier->move(
                        $this->getParameter('upload_dir'),
                        $nomFicher
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', "Impossible d'uploader le fichier");
                    return $this->redirectToRoute('user');
                }
                $user->setPhoto($nomFicher);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success","Profil modifié");
            return $this->redirectToRoute('category_index');
        }

        return $this->render('registration/edit.html.twig', [
            'user' => $user,
            'formUser' => $form->createView(),
        ]);
        
    }
}