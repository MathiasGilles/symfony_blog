<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(CategoryRepository $repo)
    {
        $categories = $repo->lastThreeCategories();

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
