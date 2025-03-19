<?php

namespace App\Command;

use App\Entity\Task;
use App\Enum\TaskPriorityEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-task-priority',
    description: 'Test the TaskPriorityEnum type conversion',
)]
class TestTaskPriorityCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("TEST 1: Valeurs valides");

        $priorities = [
            'LOW' => TaskPriorityEnum::LOW,
            'MEDIUM' => TaskPriorityEnum::MEDIUM,
            'HIGH' => TaskPriorityEnum::HIGH,
            'CRITICAL' => TaskPriorityEnum::CRITICAL,
        ];

        $taskIds = [];

        foreach ($priorities as $name => $priority) {
            $task = new Task();
            $task->setName('Test ' . $name);
            $task->setCreatedAt(new \DateTimeImmutable());
            $task->setPriority($priority);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $taskIds[$name] = $task->getId();

            $io->success(sprintf(
                "Tâche créée avec la priorité %s (%s), ID: %d",
                $name,
                $priority->value,
                $task->getId()
            ));
        }

        $this->entityManager->clear();

        $io->section("Vérification des données après relecture depuis la base de données");

        foreach ($taskIds as $name => $id) {
            $task = $this->entityManager->getRepository(Task::class)->find($id);

            if (!$task) {
                $io->error("Tâche avec ID $id non trouvée!");
                continue;
            }

            $priority = $task->getPriority();

            $io->info(sprintf(
                "Tâche ID %d: nom='%s', priorité=%s, valeur='%s', type=%s",
                $id,
                $task->getName(),
                $priority->name,
                $priority->value,
                get_class($priority)
            ));

            if ($priority instanceof TaskPriorityEnum) {
                $io->success("✓ Le type est correct (TaskPriorityEnum)");
            } else {
                $io->error("✗ Le type n'est PAS correct: " . get_class($priority));
            }

            if ($priority === $priorities[$name]) {
                $io->success("✓ La valeur est correcte");
            } else {
                $io->error(sprintf(
                    "✗ La valeur ne correspond pas: attendu=%s, obtenu=%s",
                    $priorities[$name]->value,
                    $priority->value
                ));
            }

            $io->newLine();
        }

        $io->title("TEST 2: Valeurs invalides");

        $io->section("Test d'une valeur invalide via SQL direct");

        try {
            $conn = $this->entityManager->getConnection();
            $conn->executeStatement(
                'INSERT INTO task (name, created_at, priority) VALUES (?, ?, ?)',
                ['Test Invalide SQL', new \DateTime(), 'valeur_invalide']
            );

            $io->error("La contrainte n'a pas fonctionné ! La valeur invalide a été acceptée.");
        } catch (\Throwable $e) {
            $io->success(sprintf(
                "✓ Exception correctement levée lors de l'insertion SQL invalide: %s",
                $e->getMessage()
            ));
        }

        $io->section("Test avec l'API PHP en essayant de définir une valeur invalide");

        $task = new Task();
        $task->setName('Test API Invalide');
        $task->setCreatedAt(new \DateTimeImmutable());

        try {
            $reflection = new \ReflectionProperty(Task::class, 'priority');
            $reflection->setAccessible(true);
            $reflection->setValue($task, 'valeur_invalide');

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->error("ERREUR: Aucune exception n'a été levée lors de l'injection d'une valeur invalide!");
        } catch (\Throwable $e) {
            $io->success(sprintf(
                "✓ Exception correctement levée lors de l'injection d'une valeur invalide: %s",
                $e->getMessage()
            ));
        }

        $io->note("Vous pouvez vérifier manuellement dans la base de données avec:");
        $io->text("SELECT * FROM task;");

        return Command::SUCCESS;
    }
}
