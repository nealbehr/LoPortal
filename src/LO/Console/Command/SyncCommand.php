<?php
/**
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */

namespace LO\Console\Command;

use LO\Application,
    LO\Common\BaseCrm\SyncDb,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function configure()
    {
        $this->setName('portal:sync')->setDescription('Sync with BaseCRM');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Started sync');
        $log = (new SyncDb($this->app))->user();
        $output->writeln('Finished sync');
        $output->writeln(vsprintf('Short log: created %s, updated %s, deleted %s, errors %s', $log['short_log']));
        $output->writeln(sprintf('Full log: %s', $log['full_log']));
    }
}
