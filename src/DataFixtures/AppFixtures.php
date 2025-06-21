<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly PasswordHasherFactoryInterface $passwordHasherFactory) {}


    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasherFactory
            ->getPasswordHasher(Admin::class)->hash('admin123'));
        $manager->persist($admin);

        $amsterdam = new Conference();
        $amsterdam->setCity('Amesterdam')
            ->setYear('2025')
            ->setIsInternational(true);

        $manager->persist($amsterdam);
        
        $toulouse = new Conference();
        $toulouse->setCity('Toulouse');
        $toulouse->setYear('2020');
        $toulouse->setIsInternational(false);
        $manager->persist($toulouse);
        
        $comment1 = new Comment();
        $comment1->setConference($amsterdam);
        $comment1->setAuthor('EMRICK');
        $comment1->setEmail('emrick@example.com');
        $comment1->setText('Cetait super bien passé.');
        $manager->persist($comment1);
        $manager->flush();
    }
}
