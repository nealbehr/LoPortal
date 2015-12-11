<?php
/**
 * Created by PhpStorm.
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */
namespace LO\Console\Command;

use LO\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use LO\Model\Entity\User;
use \BaseCRM\Client;
use \BaseCRM\Sync;

class SyncCommand extends Command
{
    const DEFAULT_PASSWORD = '123456';

    private $app;

    private $entityManager;

    function __construct(Application $app, $entityManager)
    {
        parent::__construct();
        $this->app           = $app;
        $this->entityManager = $entityManager;
        $this->sync          = new Sync(
            (new Client(['accessToken' => $this->app->getConfigByName('basecrm', 'accessToken')])),
            $this->app->getConfigByName('basecrm', 'devicesUuid')
        );
    }

    protected function configure()
    {
        $this->setName('portal:sync')
            ->setDescription('Sync with BaseCRM')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'It may take some time'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->entityManager->getRepository(User::class);
        $query      = $repository->createQueryBuilder('u')->where('u.email = :email');

        $this->sync->fetch(function($meta, $data) use ($query) {
            $options = [
                'table'      => $meta['type'],
                'statement'  => $meta['sync']['event_type'],
                'properties' => $data
            ];
            $a = 1;
        });


        $name = $input->getArgument('name');
        if ($name) {
            $text = 'Hello '.$name;
        }
        else {
            $text = 'Hello';
        }

        $output->writeln($text);
    }
}