<?php

declare(strict_types=1);

namespace GitlabCli\Command;

use Gitlab\Api\Projects;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectCreate extends AbstractGitlabCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('project:create')
            ->setDescription('Create a project in Gitlab')
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
        /** @var Projects $api_project */
        $api_project = self::getGitlabClient()->api('projects');

        $name = $input->getArgument('name');
        $namespace = $input->getArgument('namespace') ?? self::getDefaultNamespace();
        $namespace_id = $this->getNamespaceId($namespace);

        $project = $api_project->create($name, [
            'namespace_id' => $namespace_id,
        ]);

        $this->sendProjectResult($project, $input, $output);

        return 0;
    }
}
