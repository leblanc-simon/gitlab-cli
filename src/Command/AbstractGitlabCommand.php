<?php

declare(strict_types=1);

namespace GitlabCli\Command;

use Gitlab\Model\ProjectNamespace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGitlabCommand extends Command
{
    /**
     * @var \Gitlab\Client
     */
    private static $client = null;

    /**
     * @var string
     */
    private static $credential = null;

    /**
     * @var string
     */
    private static $endpoint = null;

    /**
     * @var string
     */
    private static $default_namespace = null;

    /**
     * Return the Gitlab Client for API.
     *
     * @return \Gitlab\Client
     */
    protected static function getGitlabClient(): \Gitlab\Client
    {
        if (null === self::$client) {
            self::$client = \Gitlab\Client::create(static::getEndpoint())
                ->authenticate(static::getCredential(), \Gitlab\Client::AUTH_URL_TOKEN)
            ;
        }

        return self::$client;
    }

    /**
     * @return string
     */
    private static function getCredential(): string
    {
        if (null === self::$credential) {
            self::readConfigFile();
        }

        return self::$credential;
    }

    /**
     * @return string
     */
    private static function getEndpoint(): string
    {
        if (null === self::$endpoint) {
            self::readConfigFile();
        }

        return self::$endpoint;
    }

    /**
     * @return string
     */
    protected static function getDefaultNamespace(): string
    {
        if (null === self::$default_namespace) {
            self::readConfigFile();
        }

        return self::$default_namespace;
    }

    /**
     * @param null|string $namespace_name
     *
     * @return int|null
     */
    protected function getNamespaceId(? string $namespace_name): ? int
    {
        if (null === $namespace_name) {
            $namespace_name = self::getDefaultNamespace();
        }

        /** @var ProjectNamespace[] $namespaces */
        $namespaces = self::getGitlabClient()->namespaces()->all([
            'search' => $namespace_name,
        ]);

        foreach ($namespaces as $namespace) {
            if ($namespace_name === $namespace['path']) {
                return $namespace['id'];
            }
        }

        return null;
    }

    protected function sendProjectResult(array $project, InputInterface $input, OutputInterface $output)
    {
        if (false === $input->getOption('all')) {
            $output->writeln($project['id']);

            return;
        }

        unset($project['tag_list']);

        $table = new Table($output);
        $table
            ->setHeaders(array_keys($project))
            ->addRow(array_values($project))
        ;
        $table->render();
    }

    /**
     * Read and the parameters.
     */
    private static function readConfigFile(): void
    {
        $filename = __DIR__.'/../../config/parameters.json';

        if (false === \file_exists($filename)) {
            throw new \RuntimeException('configuration file do not exist !');
        }

        $datas = \json_decode(\file_get_contents($filename), true);

        if (
            false === isset($datas['endpoint'])
            || false === isset($datas['api_token'])
            || false === isset($datas['default_namespace'])
        ) {
            throw new \RuntimeException('endpoint and api_token must be defined !');
        }

        self::$endpoint = $datas['endpoint'];
        self::$credential = $datas['api_token'];
        self::$default_namespace = $datas['default_namespace'];
    }
}
