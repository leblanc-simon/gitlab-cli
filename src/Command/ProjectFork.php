<?php

declare(strict_types=1);

namespace GitlabCli\Command;

use Gitlab\Api\Projects;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectFork extends AbstractGitlabCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('project:fork')
            ->setDescription('Fork a project in Gitlab')
            ->addArgument('id', InputArgument::REQUIRED, 'The parent project ID', null)
            ->addArgument('namespace', InputArgument::REQUIRED, 'The namespace of the project', null)
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

        $project_id = $input->getArgument('id');
        $namespace = $input->getArgument('namespace');

        $project = $api_project->fork($project_id, [
            'namespace' => $namespace,
        ]);

        $this->sendProjectResult($project, $input, $output);

        return 0;
    }
}
