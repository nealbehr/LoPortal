<?php
/**
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */
namespace LO\Console\Command;

use LO\Application;
use LO\Common\BaseCrm\SyncDb;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $output->writeln(
            vsprintf('Finished sync. (Created: %s | Updated: %s | Deleted %s)', (new SyncDb($this->app))->user())
        );
    }
}
