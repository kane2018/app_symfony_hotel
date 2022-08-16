<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Image;
use Bluemmb\Faker\PicsumPhotosProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    private $hacher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hacher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Fatou')
            ->setLastName('KANE')
            ->setEmail('fatou.kane.aida@gmail.com')
            ->setHash($this->hacher->hashPassword($adminUser, 'password'))
            ->setPicture($faker->imageUrl(64, 64))
            ->setIntroduction($faker->sentence())
            ->setDescription('<p>' . join("<p></p>", $faker->paragraphs(5, false)) . '</p>')
            ->addUserRole($adminRole);
        $manager->persist($adminUser);

        $users = [];

        $genres = ["male", "female"];

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';

            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $hash = $this->hacher->hashPassword($user, 'password');

            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->email())
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>' . join("<p></p>", $faker->paragraphs(5, false)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
        }


        for ($i = 1; $i <= 30; $i++) {

            $ad = new Ad();

            $title = $faker->sentence();

            $coverImage = $faker->imageUrl(1000, 350);
            $introduction = $faker->paragraph(2);
            $content = '<p>' . join("<p></p>", $faker->paragraphs(5, false)) . '</p>';

            $user = $users[mt_rand(0, count($users) - 1)];

            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContenu($content)
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5))
                ->setAuthor($user);

            for ($j = 1; $j <= mt_rand(2, 5); $j++) {
                $image = new Image();

                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);

                $manager->persist($image);
            }


            //Réservations
            for ($j = 1; $j <= mt_rand(0, 10); $j) {
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');

                $duration = mt_rand(3, 10);

                $endDate = (clone $startDate)->modify("+$duration days");

                $amount = $ad->getPrice() * $duration;

                $booker = $users[mt_rand(0, count($users) - 1)];

                $comment = $faker->paragraph();

                $booking->setBooker($booker)
                    ->setAd($ad)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setCreatedAt($createdAt)
                    ->setAmount($amount)
                    ->setComment($comment);

                $manager->persist($booking);
                if(mt_rand(0, 1)){
                    $comment = new Comment();
                    $comment->setContenu($faker->paragraph())
                        ->setRating(mt_rand(1, 5))
                        ->setAuthor($booker)
                        ->setAd($ad);

                    $manager->persist($comment);
                }
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
