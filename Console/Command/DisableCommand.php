<?php

declare(strict_types=1);

namespace Fom\Clockwork\Console\Command;

use Fom\Clockwork\Model\Config\DatabaseLogger;
use Fom\Clockwork\Model\Config\ProfilerFlag;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableCommand extends Command
{
    /**
     * Command name to disable Clockwork profiler.
     */
    private const COMMAND_NAME = 'dev:clockwork:disable';

    /**
     * @var ProfilerFlag
     */
    private $profilerFlag;

    /**
     * @var DatabaseLogger
     */
    private $databaseLogger;

    /**
     * @param ProfilerFlag $profilerFlag
     * @param DatabaseLogger $databaseLogger
     * @param string $name
     */
    public function __construct(
        ProfilerFlag $profilerFlag,
        DatabaseLogger $databaseLogger,
        string $name = self::COMMAND_NAME
    ) {
        parent::__construct($name);
        $this->profilerFlag = $profilerFlag;
        $this->databaseLogger = $databaseLogger;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Disables Clockwork.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->profilerFlag->disable();
        $this->databaseLogger->disable();

        $output->writeln("<info>Clockwork disabled.</info>");
    }
}
