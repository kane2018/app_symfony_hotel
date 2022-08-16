<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    #[Route('/login', name: 'account_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $username = $authenticationUtils->getLastUsername();

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    #[Route('/logout', name: 'account_logout')]
    public function logout() {
        //
    }

    #[Route('/register', name: 'account_register')]
    public function register(Request $request,EntityManagerInterface $manager, UserPasswordHasherInterface $hasher) : Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $hash = $hasher->hashPassword($user, $user->getHash());

            $user->setHash($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success', 'Votre compte a été bien crée ! Vous pouvez maintenant vous connecter !'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route("/account/profile", name: "account_profile")]
    #[IsGranted("ROLE_USER")]
    public function profile(Request $request, EntityManagerInterface $manager) : Response {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route("/account/password-update", name: "account_password")]
    #[IsGranted("ROLE_USER")]
    public function updatePassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager) {
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())){
                $form->get('oldPassword')->addError(new FormError('Mot de passe n\'est pas votre mot de passe actuel'));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();

                $hash = $hasher->hashPassword($user, $newPassword);

                $user->setHash($hash);

                $manager->persist($user);

                $manager->flush();

                $this->addFlash('success', 'Votre mot de passe a été bien modifiée');

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('account/password.html.twig',
        [
            'form' => $form->createView()
        ]);
    }
    #[Route("/account", name: "account_index")]
    #[IsGranted("ROLE_USER")]
    public function myAccount() {
        return $this->render('user/index.html.twig',
            [
                'user' => $this->getUser()
            ]);
    }

    #[Route("/account/bookings", name: 'account_bookings')]
    public function bookins()
    {
        return $this->render('account/bookings.html.twig');
    }
}
