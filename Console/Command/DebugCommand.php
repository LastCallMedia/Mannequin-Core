<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Core\Console\Command;

use LastCall\Mannequin\Core\ConfigInterface;
use LastCall\Mannequin\Core\Ui\ManifestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class DebugCommand extends Command
{
    private $config;
    private $builder;

    public function __construct($name, ManifestBuilder $builder, ConfigInterface $config)
    {
        parent::__construct($name);
        $this->builder = $builder;
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $resolver = $this->config->getVariableResolver();
        $io->block('Variable Types');
        $io->table(
            ['Type', 'Description'],
            $this->splitResolverDescription($resolver->describe())
        );

        $io->block('Patterns');
        $manifest = $this->builder->generate($this->config->getCollection());
        $yaml = Yaml::dump($manifest['patterns'], 5);
        $output->write($yaml);
    }

    private function splitResolverDescription(array $description) {
        $cells = [];
        foreach($description as $type => $description) {
            $cells[] = [$type, $description];
        }
        return $cells;
    }
}
