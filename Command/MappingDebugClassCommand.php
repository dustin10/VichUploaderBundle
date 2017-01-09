<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MappingDebugClassCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('vich:mapping:debug-class')
            ->setDescription('Debug a class.')
            ->addArgument('fqcn', InputArgument::REQUIRED, 'The FQCN of the class to debug.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $metadataReader = $this->getContainer()->get('vich_uploader.metadata_reader');
        $fqcn = $input->getArgument('fqcn');

        if (!$metadataReader->isUploadable($fqcn)) {
            $output->writeln(sprintf('<error>"%s" is not uploadable.</error>', $fqcn));

            return 1;
        }

        $uploadableFields = $metadataReader->getUploadableFields($fqcn);

        $output->writeln(sprintf('Introspecting class <info>%s</info>:', $fqcn));
        foreach ($uploadableFields as $data) {
            $output->writeln(sprintf('Found field "<comment>%s</comment>", storing file name in <comment>"%s</comment>" and using mapping "<comment>%s</comment>"', $data['propertyName'], $data['fileNameProperty'], $data['mapping']));
        }
    }
}
