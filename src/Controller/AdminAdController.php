<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAdController extends AbstractController
{
    #[Route('/admin/ads/{page<\d+>?1}', name: 'admin_ads_index')]
    public function index(AdRepository $repository, $page, PaginationService $paginationService): Response
    {
        $paginationService->setEntityClass(Ad::class)
            ->setCurrentPage($page);

        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $paginationService
        ]);
    }

    #[Route("/admin/ads/{id}/edit", name: "admin_ads_edit")]
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a été bien enregistrée");
        }

        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/ads/{id}/delete', name: 'admin_ads_delete')]
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        if(count($ad->getBookings()) > 0) {
            $this->addFlash('warning', "Vous ne pouvez pas supprimer l'annonce {$ad->getTitle()} car elle a déjà des réservations");
        } else {
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash('success', "L'annonce {$ad->getTitle()} a été bien supprimée");
        }

        return $this->redirectToRoute('admin_ads_index');
    }
}
