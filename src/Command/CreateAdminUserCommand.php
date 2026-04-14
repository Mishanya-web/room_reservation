<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a new admin user with ROLE_ADMIN',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'User email')
            ->addArgument('phone', InputArgument::OPTIONAL, 'User phone')
            ->addArgument('name', InputArgument::OPTIONAL, 'User name')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Email
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Enter email: ');
            $question->setValidator(function ($value) {
                if (empty($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Valid email is required');
                }
                return $value;
            });
            $email = $io->askQuestion($question);
        }

        // Phone
        $phone = $input->getArgument('phone');
        if (!$phone) {
            $question = new Question('Enter phone (11 digits, starts with 7 or 8): ');
            $question->setValidator(function ($value) {
                if (empty($value) || !preg_match('/^[78]\d{10}$/', $value)) {
                    throw new \RuntimeException('Valid phone is required (11 digits, starts with 7 or 8)');
                }
                return $value;
            });
            $phone = $io->askQuestion($question);
        }

        // Name
        $name = $input->getArgument('name');
        if (!$name) {
            $question = new Question('Enter name: ');
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \RuntimeException('Name is required');
                }
                return $value;
            });
            $name = $io->askQuestion($question);
        }

        // Password
        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Enter password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                if (empty($value) || strlen($value) < 6) {
                    throw new \RuntimeException('Password must be at least 6 characters');
                }
                return $value;
            });
            $password = $io->askQuestion($question);
        }

        // Check existing
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->error("User with email {$email} already exists!");
            return Command::FAILURE;
        }

        $existingUserByPhone = $this->entityManager->getRepository(User::class)
            ->findOneBy(['phone' => $phone]);

        if ($existingUserByPhone) {
            $io->error("User with phone {$phone} already exists!");
            return Command::FAILURE;
        }

        // Create admin
        $user = new User();
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setName($name);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user created! Email: %s, Phone: %s', $email, $phone));
        $io->note('Login to admin panel at /admin');

        return Command::SUCCESS;
    }
}
