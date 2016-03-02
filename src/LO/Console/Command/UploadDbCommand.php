<?php
/**
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */

namespace LO\Console\Command;

use LO\Application,
    LO\Common\BaseCrm\UploadDb,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class UploadDbCommand extends Command
{
    function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function configure()
    {
        $this->setName('base:upload')->setDescription('Upload to BaseCRM');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Started upload data to BaseCRM');
        $log = (new UploadDb($this->app))->contacts();
        $output->writeln(vsprintf('Short log: updated %s, not found %s', $log));
        $output->writeln('Finished upload');
    }
}
