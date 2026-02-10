<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class McpServerCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('mcp-server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $server = Server::make()
            ->withServerInfo('Behat MCP Server', '1.0.0')
            ->build();

        $server->discover(
            basePath: dirname(__DIR__),
            scanDirs: ['Mcp']
        );

        $transportType = $input->getOption('mcp-transport');

        if ($transportType === 'http') {
            $host = $input->getOption('mcp-host');
            $port = (int) $input->getOption('mcp-port');

            $transport = new StreamableHttpServerTransport(
                host: $host,
                port: $port,
            );
        } else {
            $transport = new StdioServerTransport();
        }

        $server->listen($transport);

        return 0;
    }
}
