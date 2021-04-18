<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;

class MappingDebugClassCommand extends Command
{
    protected static $defaultName = 'vich:mapping:debug-class';

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
            ->setName('vich:mapping:debug-class')
            ->setDescription('Debug a class.')
            ->addArgument('fqcn', InputArgument::REQUIRED, 'The FQCN of the class to debug.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fqcn = $input->getArgument('fqcn');

        if (!$this->metadataReader->isUploadable($fqcn)) {
            $output->writeln(\sprintf('<error>"%s" is not uploadable.</error>', $fqcn));

            return 1;
        }

        $uploadableFields = $this->metadataReader->getUploadableFields($fqcn);

        $output->writeln(\sprintf('Introspecting class <info>%s</info>:', $fqcn));
        foreach ($uploadableFields as $data) {
            $output->writeln(\sprintf('Found field "<comment>%s</comment>", storing file name in <comment>"%s</comment>" and using mapping "<comment>%s</comment>"', $data['propertyName'], $data['fileNameProperty'], $data['mapping']));
        }

        return 0;
    }
}
