<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/new/{id}",name="comment_new")
     */
    public function create(Request $request,$id,ArticleRepository $repo)
    {
        $comment = new Comment;
        $article = $repo->findOneBy(['id' => $id]);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setStatus(true)
                    ->setPublishedAt(new \DateTime())
                    ->setAuthor($this->getUser())
                    ->setArticle($article);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash("success","Commentaire ajouté");

            return $this->redirectToRoute('article_index');
        }

        return $this->render('comment/new.html.twig', [
            'formComment' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comment/delete/{id}",name="comment_delete")
     */
    public function delete($id = null, Request $request, CommentRepository $repo)
    {

        if ($id != null) {
            $comment = $repo->find($id);
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($comment);
            $manager->flush();
        }
        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/comment/status/{id}",name="comment_status")
     */
    public function status($id = null, Request $request, CommentRepository $repo)
    {

        if ($id != null) {
            $comment = $repo->find($id);
            $manager = $this->getDoctrine()->getManager();
            if ($comment->getStatus() == false) {
                $comment->setStatus(true);
            }
            else {
                $comment->setStatus(false);
            }
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash("success","Commentaire modifié");
        }
        return $this->redirectToRoute('article_index');
    }
}
