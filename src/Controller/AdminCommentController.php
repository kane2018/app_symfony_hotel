<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentController extends AbstractController
{
    #[Route('/admin/comments/{page<\d+>?1}', name: 'admin_comment_index')]
    public function index(CommentRepository $repository, $page, PaginationService $paginationService): Response
    {
        $paginationService->setEntityClass(Comment::class)
            ->setCurrentPage($page);
        return $this->render('admin/comment/index.html.twig', [
            'pagination' => $paginationService,
        ]);
    }

    #[Route("/admin/comments/{id}/edit", name: 'admin_comment_edit')]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminCommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', "Le commentaire numéro {$comment->getId()} a été bien modifiée");
        }

        return $this->render('admin/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    #[Route("/admin/comments/{id}/delete", name: "admin_comment_delete")]
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash('success', 'Le commentaire a été bien supprimé');

        return $this->redirectToRoute('admin_comment_index');
    }
}
