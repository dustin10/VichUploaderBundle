<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;

#[AsCommand(name: 'vich:mapping:list-classes')]
final class MappingListClassesCommand extends Command
{
    public function __construct(private readonly MetadataReader $metadataReader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('vich:mapping:list-classes')
            ->setDescription('Searches for uploadable classes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Looking for uploadable classes.');

        $uploadableClasses = $this->metadataReader->getUploadableClasses();

        foreach ($uploadableClasses as $class) {
            $output->writeln(\sprintf('Found <comment>%s</comment>', $class));
        }

        $output->writeln(\sprintf('Found <comment>%d</comment> classes.', \count((array) $uploadableClasses)));

        return self::SUCCESS;
    }
}
