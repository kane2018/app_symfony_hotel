<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminBookingController extends AbstractController
{
    #[Route('/admin/bookings/{page<\d+>?1}', name: 'admin_booking_index')]
    public function index(BookingRepository $bookingRepository, $page, PaginationService $paginationService): Response
    {

        $paginationService->setEntityClass(Booking::class)
            ->setCurrentPage($page)
        ->setLimit(20);


        return $this->render('admin/booking/index.html.twig', [
            'pagination' => $paginationService
        ]);
    }

    #[Route("/admin/bookings/{id}/edit", name: "admin_booking_edit")]
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminBookingType::class, $booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $booking->setAmount(0);

            $manager->persist($booking);
            $manager->flush();

            $this->addFlash('success', "La réservation n°{$booking->getId()} a été bien modifiée");

            return $this->redirectToRoute('admin_booking_index');
        }

        return $this->render('admin/booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView()
        ]);
    }

    #[Route('admin/bookings/{id}/delete', name: 'admin_booking_delete')]
    public function delete(Booking $booking, EntityManagerInterface $manager)
    {
        $manager->remove($booking);
        $manager->flush();

        $this->addFlash('success', 'La réservation a été bien supprimée');

        return $this->redirectToRoute('admin_booking_index');
    }
}
