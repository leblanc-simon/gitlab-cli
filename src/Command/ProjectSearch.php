<?php

declare(strict_types=1);

namespace GitlabCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectSearch extends AbstractGitlabCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('project:search')
            ->setDescription('Search a project by this name')
            ->addArgument('name', InputArgument::REQUIRED, 'The project name', null)
            ->addArgument('namespace', InputArgument::OPTIONAL, 'The namespace of the project', null)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Return all data about project')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = self::getGitlabClient()->projects()->all([
            'simple' => true,
            'search' => $input->getArgument('name'),
        ]);

        $namespace = $input->getArgument('namespace') ?? self::getDefaultNamespace();

        $path_with_namespace = $namespace.'/'.$input->getArgument('name');

        foreach ($projects as $project) {
            if (false === isset($project['path_with_namespace'])) {
                continue;
            }

            if ($path_with_namespace === $project['path_with_namespace']) {
                $this->sendProjectResult($project, $input, $output);

                return 0;
            }
        }

        return 1;
    }
}
