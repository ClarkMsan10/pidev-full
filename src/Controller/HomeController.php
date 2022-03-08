<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\TechnologyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="app_home")
     */
    public function index(ProjectRepository $projectRepository, CategoryRepository $categoryRepository, TechnologyRepository $technologyRepository): Response
    {

        $projects = $projectRepository->findAll();

        $categories = $categoryRepository->findAll();

        $technologies = $technologyRepository->findAll();

        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'projects'=> $projects,
            'categories'=> $categories,
            'technologies'=> $technologies
        ]);
    }

    /**
     * @Route("/show", name="app_show")
     */
    public function show(ProjectRepository $projectRepository, CategoryRepository $categoryRepository, TechnologyRepository $technologyRepository): Response
    {


        
        return $this->render('home/show.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }




}
