<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<5; $i++){
            $user = new User;
            $user->setName("Utilisateur N°" . $i);
            $user->setPassword("password" . $i);

            $manager->persist($user);
            $users[]= $user;
        }

        for($i=0; $i<20; $i++){
            $note = new Note;
            $note->setContenu("contenu de la note N°" . $i);
            $note->setCreatedAt(new DateTimeImmutable());
            $note->setIsOver(false);
            $note->setUser($users[array_rand($users)]);
            $manager->persist($note);
        }

        $manager->flush();
    }
}
