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
use LO\Model\Entity\Lender;
use \BaseCRM\Client;
use \BaseCRM\Sync;
use Doctrine\ORM\NoResultException;

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
        $qUser = $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.base_id = :id');

        $qLender = $this->entityManager
            ->getRepository(Lender::class)
            ->createQueryBuilder('l')
            ->where('l.name = :name');

        $notLender = $qLender->setParameter('name', 'Not Lender')->getQuery()->getSingleResult();

        $this->sync->fetch(function($meta, $data) use ($qUser, $qLender, $notLender) {
            if ($meta['type'] === 'contact' && isset($data['id'], $data['email'])) {
                if (isset($data['custom_fields']['Sub-Company Name (DBA)'])) {
                    try {
                        $lender = $qLender->setParameter('name', $data['custom_fields']['Sub-Company Name (DBA)'])
                            ->getQuery()
                            ->getSingleResult();
                    }
                    catch (NoResultException $e) {
                        $lender = $notLender;
                    }
                }
                else {
                    $lender = $notLender;
                }

                if (isset($lender)) {
                    // Update user
                    try {
                        $user = $qUser->setParameter('id', $data['id'])->getQuery()->getSingleResult();
                    }
                        // New user
                    catch (NoResultException $e) {
                        $user = new User;
                        $user->setPassword(self::DEFAULT_PASSWORD);
                    }
                    $user->setBaseId($data['id']);
                    $user->setEmail($data['email']);
                    $user->setPhone($data['phone']);
                    $user->setMobile($data['mobile']);
                    if (isset($data['custom_fields']['NMLS'])) {
                        $user->setNmls($data['custom_fields']['NMLS']);
                    }
                    if (isset($data['custom_fields']['Sales Director'])) {
                        $user->setSalesDirector($data['custom_fields']['Sales Director']);
                    }
                    $user->setTitle($data['title']);
                    $user->setFirstName($data['first_name']);
                    $user->setLastName($data['last_name']);
                    $user->setLender($lender);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }
            }
        });

        $output->writeln('Finished sync');
    }
}