<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Atelier;
use App\Entity\User;
use Faker\Factory;
class AtelierFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');
        $users = [];


        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setNom($faker->firstName);
            $user->setPrenom($faker->lastName);
            $user->setEmail($faker->email);
            $user->setPassword($faker->password);
            $user->setRoles(['ROLE_INSTRUCTEUR']);
            $users [] = $user;
            $manager->persist($user);
        }
        $manager->flush();

        for ($i = 0; $i < 100; $i++) {
            $atelier = new Atelier();
            $atelier->setNom($faker->company);
            $atelier->setDescription($faker->bs);
            $atelier->setUser($users[$faker->numberBetween(0, 49)]);
            //ajouter quelques participants différents de l'instructeur
            $participants = [];
            for ($j = 0; $j < $faker->numberBetween(0, 14); $j++) {
                $participant = $users[$faker->numberBetween(0, 49)];
                if (!in_array($participant, $participants)) {
                    $participants[] = $participant;
                }
            }
            foreach ($participants as $participant) {
                $atelier->addParticipant($participant);
            }
            $manager->persist($atelier);
        }
        $manager->flush();


        //ajouter admin
        $admin = new User();
        $admin->setNom('admin');
        $admin->setPrenom('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setPassword('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();


    }
}
