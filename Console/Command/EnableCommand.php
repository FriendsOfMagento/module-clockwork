<?php

declare(strict_types=1);

namespace Fom\Clockwork\Console\Command;

use Fom\Clockwork\Model\Config\DatabaseLogger;
use Fom\Clockwork\Model\Config\ProfilerFlag;
use Magento\Developer\Console\Command\QueryLogEnableCommand;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestionFactory;

class EnableCommand extends Command
{
    /**
     * Command name to enable Clockwork profiler.
     */
    private const COMMAND_NAME = 'dev:clockwork:enable';

    /**
     * @var ProfilerFlag
     */
    private $profilerFlag;

    /**
     * @var DatabaseLogger
     */
    private $databaseLogger;

    /**
     * @var ConfirmationQuestionFactory
     */
    private $confirmationQuestionFactory;

    /**
     * @param ProfilerFlag $profilerFlag
     * @param DatabaseLogger $databaseLogger
     * @param ConfirmationQuestionFactory $confirmationQuestionFactory
     * @param string $name
     */
    public function __construct(
        ProfilerFlag $profilerFlag,
        DatabaseLogger $databaseLogger,
        ConfirmationQuestionFactory $confirmationQuestionFactory,
        string $name = self::COMMAND_NAME
    ) {
        parent::__construct($name);
        $this->profilerFlag = $profilerFlag;
        $this->databaseLogger = $databaseLogger;
        $this->confirmationQuestionFactory = $confirmationQuestionFactory;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Enables Clockwork which includes a profiler and database logger.');
        $this->setDefinition(
            [
                new InputOption(
                    QueryLogEnableCommand::INPUT_ARG_LOG_ALL_QUERIES,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Log all queries. [true|false]',
                    'true'
                ),
                new InputOption(
                    QueryLogEnableCommand::INPUT_ARG_LOG_QUERY_TIME,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Query time thresholds.',
                    '0.001'
                ),
                new InputOption(
                    QueryLogEnableCommand::INPUT_ARG_LOG_CALL_STACK,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Include call stack. [true|false]',
                    'false'
                ),
            ]
        );

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
        if ($this->canEnable($input, $output)) {
            $this->profilerFlag->enable();

            $logAllQueries = filter_var(
                $input->getOption(QueryLogEnableCommand::INPUT_ARG_LOG_ALL_QUERIES),
                FILTER_VALIDATE_BOOLEAN
            );
            $logQueryTime = (float)$input->getOption(QueryLogEnableCommand::INPUT_ARG_LOG_QUERY_TIME);
            $logCallStack = filter_var(
                $input->getOption(QueryLogEnableCommand::INPUT_ARG_LOG_CALL_STACK),
                FILTER_VALIDATE_BOOLEAN
            );

            $this->databaseLogger->enable($logAllQueries, $logQueryTime, $logCallStack);

            $output->writeln("<info>Profiler enabled with Clockwork output.</info>");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function canEnable(InputInterface $input, OutputInterface $output): bool
    {
        $profilerFlagConfigured = $this->profilerFlag->isNeedToOverride();
        $databaseLoggerConfigured = $this->databaseLogger->isNeedToOverride();

        if (!$profilerFlagConfigured && !$databaseLoggerConfigured) {
            return true;
        }

        return $this->askToOverride($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function askToOverride(InputInterface $input, OutputInterface $output): bool
    {
        $questionText = implode(
            ' ',
            [
                'Another profiling driver is currently active.',
                'Do you want to use Clockwork instead of the current one?',
                '[Y/n]:',
            ]
        );

        $question = $this->confirmationQuestionFactory->create(
            ['question' => '<question>' . $questionText . '</question>']
        );

        return $this->getHelper('question')->ask($input, $output, $question);
    }
}
