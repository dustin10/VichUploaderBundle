<?php

namespace Vich\UploaderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vich\UploaderBundle\Exception\MappingNotFoundException;

class MappingDebugCommand extends Command
{
    protected static $defaultName = 'vich:mapping:debug';

    /** @var array */
    private $mappings;

    public function __construct(array $mappings)
    {
        parent::__construct();
        $this->mappings = $mappings;
    }

    protected function configure(): void
    {
        $this
            ->setName('vich:mapping:debug')
            ->setDescription('Debug a mapping.')
            ->addArgument('mapping', InputArgument::REQUIRED, 'The mapping to debug.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mapping = $input->getArgument('mapping');

        if (!isset($this->mappings[$mapping])) {
            throw new MappingNotFoundException(\sprintf('Mapping "%s" does not exist.', $mapping));
        }

        $output->writeln(\sprintf('Debug information for mapping <info>%s</info>', $mapping));

        foreach ($this->mappings[$mapping] as $key => $value) {
            $output->writeln(\sprintf('<comment>%s</comment>: %s', $key, \var_export($value, true)));
        }

        return 0;
    }
}
