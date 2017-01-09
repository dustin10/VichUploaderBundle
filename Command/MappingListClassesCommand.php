<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MappingListClassesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('vich:mapping:list-classes')
            ->setDescription('Searches for uploadable classes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Looking for uploadable classes.');

        $metadataReader = $this->getContainer()->get('vich_uploader.metadata_reader');
        $uploadableClasses = $metadataReader->getUploadableClasses();

        foreach ($uploadableClasses as $class) {
            $output->writeln(sprintf('Found <comment>%s</comment>', $class));
        }

        $output->writeln(sprintf('Found <comment>%d</comment> classes.', count($uploadableClasses)));
        $output->writeln('<info>NOTE:</info> Only classes configured using XML or YAML are displayed.');
    }
}
