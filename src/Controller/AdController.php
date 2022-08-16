<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ad;
use App\Repository\AdRepository;
use App\Form\AdType;

class AdController extends AbstractController
{
    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $adrepo): Response
    {
       
        $ads = $adrepo->findAll();

        
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }
    
    #[Route('/ads/new', name: 'ads_create')]
    #[IsGranted("ROLE_USER")]
    public function create(Request $request, ManagerRegistry $doctrine) {
        
        $ad = new Ad();

        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        $manager = $doctrine->getManager();

        if($form->isSubmitted() && $form->isValid()) {

            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a été enregistrée"
            );

            return $this->redirectToRoute('ads_show', ['slug' => $ad->getSlug()]);
        }

        return $this->render('ad/new.html.twig',
        [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/ads/{slug}', name: 'ads_show')]
    public function show(Ad $ad) {
        
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    #[Route('/ads/{slug}/edit', name: 'ads_edit')]
    #[Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message: "Cette annonce ne vous appartient pas")]
    public function edit(Ad $ad, Request $request, ManagerRegistry $doctrine) {

        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        $manager = $doctrine->getManager();

        if($form->isSubmitted() && $form->isValid()) {

            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);

                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a été modifiée"
            );

            return $this->redirectToRoute('ads_show', ['slug' => $ad->getSlug()]);
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);

    }

    #[Route("/ads/delete/{slug}", name: 'ads_delete')]
    #[Security("is_granted('ROLE_USER') and user == ad.getAuthor()")]
    public function delete(Ad $ad, EntityManagerInterface $manager) {

        $manager->remove($ad);
        $manager->flush();

        $this->addFlash('success', 'L\'annonce '.$ad->getTitle().' a bien été supprimée');

        return $this->redirectToRoute('ads_index');
    }
    
}
