<?php

namespace Vich\UploaderBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vich\UploaderBundle\Tests\Functional\WebTestCase;

abstract class AbstractCommandTestCase extends WebTestCase
{
    protected function executeCommand(string $name, Command $command, array $arguments = []): string
    {
        $client = self::createClient();
        $application = new Application($client->getKernel());
        // @phpstan-ignore-next-line function.alreadyNarrowedType
        if (\method_exists(Application::class, 'addCommand')) {
            $application->addCommand($command);
        } else {
            $application->add($command);    // @phpstan-ignore-line method.notFound
        }
        $cmd = $application->find($name);
        $commandTester = new CommandTester($cmd);
        $commandTester->execute(\array_merge(['command' => $cmd->getName()], $arguments));

        return $commandTester->getDisplay();
    }
}
