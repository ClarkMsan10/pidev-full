<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vote;
use App\Entity\Comment;
use App\Entity\Project;
use App\Form\CommentType;
use App\Form\ProjectType;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;

/**
 * @Route("/project")
 */
class ProjectController extends AbstractController
{

    
    /**
     * @var Security
     */
    private $security;

    /**
     * @Route("/", name="app_project_index", methods={"GET"})
     */
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    public function __construct(Security $security)
    {
        $this->security = $security ;
    }

    /**
     * @Route("/new", name="app_project_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProjectRepository $projectRepository): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $project->setSlug($this->slugify($project->getTitle()));
            $project->setTitle($this->capitalize($project->getTitle()));

            $user = $this->security->getUser();

            $project->setPublishedBy($user);
            
            $projectRepository->add($project);
            return $this->redirectToRoute('app_project_show', ['slug'=>$project->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{slug}", name="app_project_show", methods={"GET","POST"})
     */
    public function show(Request $requestc, Project $project, VoteRepository $voteRepository, UserRepository $userRepository, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $formCommentaire = $this->createForm(CommentType::class, $comment);
        $formCommentaire->handleRequest($requestc);
        if ($formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
            $comment->setProject($project);
            $comment->setCommentBy($this->security->getUser());
            $commentRepository->add($comment);
            return $this->redirectToRoute('app_project_show', ['slug'=>$project->getSlug()], Response::HTTP_SEE_OTHER);
        }

        $result = $this->verify($project, $voteRepository, $userRepository);
        $r = false;
        if($result != null){
            $r = true;
        }

        return $this->renderForm('project/show.html.twig', [
            'project' => $project,
            'hasVoted'=> $r,
            'comment' => $comment,
            'formC' => $formCommentaire
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="app_project_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $project->setSlug($this->slugify($project->getTitle()));
            $project->setTitle($this->capitalize($project->getTitle()));

            $user = $this->security->getUser();

            $project->setPublishedBy($user);

            $projectRepository->add($project);
            return $this->redirectToRoute('app_project_show', ['slug'=>$project->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/{slug}/voter", name="app_project_vote", methods={"GET", "POST"})
     */
    public function voter(Request $request, Project $project, ProjectRepository $projectRepository, VoteRepository $voteRepository): Response
    {
        
        $project->setNbreVote($project->getNbreVote() + 1);
        $vote = new Vote();
        $vote->setProject($project);
        $vote->setVotedBy($this->security->getUser());

        $projectRepository->add($project);
        $voteRepository->add($vote);

        return $this->redirectToRoute('app_project_show', ['slug'=> $project->getSlug()], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/{slug}/devoter", name="app_project_devote", methods={"GET", "POST"})
     */
    public function devoter(Request $request, Project $project, ProjectRepository $projectRepository, VoteRepository $voteRepository, UserRepository $userRepository ): Response
    {
        $vote = $this->verify($project, $voteRepository, $userRepository);
        $project->removeVote($vote);
        $voteRepository->remove($vote);
        $project->setNbreVote($project->getNbreVote() - 1);
        $projectRepository->add($project);


        return $this->redirectToRoute('app_project_show', ['slug'=> $project->getSlug()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}", name="app_project_delete", methods={"POST"})
     */
    public function delete(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $projectRepository->remove($project);
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }


    public static function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function capitalize($text)
    {
        // lowercase
        $text = strtolower($text);

        $text = ucwords($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function verify(Project $project, VoteRepository $voteRepository, UserRepository $userRepository){
  
        $user = $this->security->getUser();
        $votes = $project->getVotes();

        $user = $userRepository->findOneByEmail($user->getUserIdentifier());
        $vote = $voteRepository->findOneBySomeField($user , $project);

        return $vote;

    }



}
