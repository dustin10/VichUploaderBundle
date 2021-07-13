<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;

/**
 * @final
 */
class MappingListClassesCommand extends Command
{
    protected static $defaultName = 'vich:mapping:list-classes';

    /** @var MetadataReader */
    private $metadataReader;

    public function __construct(MetadataReader $metadataReader)
    {
        parent::__construct();
        $this->metadataReader = $metadataReader;
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

        $output->writeln(\sprintf('Found <comment>%d</comment> classes.', \count($uploadableClasses)));
        $output->writeln('<info>NOTE:</info> Only classes configured using XML or YAML are displayed.');

        return 0;
    }
}
