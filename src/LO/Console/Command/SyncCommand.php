<?php
/**
 * Created by PhpStorm.
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */
namespace LO\Console\Command;

use LO\Application;
use LO\Model\Entity\User;
use LO\Model\Entity\Lender;
use LO\Model\Entity\Address;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \BaseCRM\Client;
use \BaseCRM\Sync;
use Doctrine\ORM\NoResultException;

class SyncCommand extends Command
{
    const DEFAULT_PASSWORD = '123456';
    const GOOGLE_API       = 'https://maps.googleapis.com/maps/api/place/';

    private $syncAddress   = true;

    private $app;
    private $entityManager;
    private $sync;

    /**
     * Counters
     *
     * @var int
     */
    private $countUpdate   = 0;
    private $countCreate   = 0;
    private $countDelete   = 0;

    function __construct(Application $app)
    {
        parent::__construct();
        $this->app           = $app;
        $this->entityManager = $app->getEntityManager();
        $this->sync          = new Sync(
            (new Client(['accessToken' => $this->app->getConfigByName('basecrm', 'accessToken')])),
            $this->app->getConfigByName('basecrm', 'devicesUuid')
        );
    }

    protected function configure()
    {
        $this->setName('portal:sync')->setDescription('Sync with BaseCRM');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qUser   = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')->where('u.base_id = :id');
        $qLender = $this->entityManager->getRepository(Lender::class)->createQueryBuilder('l')->where('l.name = :name');

        // Get or create "Not Lender" company
        $notLenderName = 'Not Lender';
        try {
            $notLender = $qLender->setParameter('name', $notLenderName)->getQuery()->getSingleResult();
        }
        catch (NoResultException $e) {
            $notLender = new Lender();
            $notLender->setName($notLenderName);
            $this->entityManager->persist($notLender);
            $this->entityManager->flush();
        }

        $this->sync->fetch(function($meta, $data) use ($qUser, $qLender, $notLender) {
            // Sync contacts
            if ($meta['type'] === 'contact' && isset($data['id'], $data['email'])) {
                // Update user
                try {
                    $user    = $qUser->setParameter('id', $data['id'])->getQuery()->getSingleResult();
                    $address = $user->getAddress();
                    $this->countUpdate++;
                }
                // Create user
                catch (NoResultException $e) {
                    $user    = new User;
                    $address = new Address;
                    $user->setBaseId($data['id']);
                    $user->setPassword(self::DEFAULT_PASSWORD);
                    $this->countCreate++;
                }
                $address->setBaseOriginalAddress(implode(', ', $data['address']));

                // Set lender data
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
                $user->setLender($lender);

                // Create/update full user data
                if (
                    (
                        isset($data['custom_fields']['Active PMP'])
                        && 'yes' === strtolower($data['custom_fields']['Active PMP'])
                    )
                    || (
                        isset($data['custom_fields']['Sub-Company Name (DBA)'])
                        && 'firstrex' === strtolower($data['custom_fields']['Sub-Company Name (DBA)'])
                    )
                ) {
                    // Set address data
                    $originalAddress = implode(', ', $data['address']);
                    if ($this->syncAddress
                        && $address->getBaseOriginalAddress() !== $originalAddress
                        && ($googleAddress = $this->getAddressViaTextSearch($originalAddress))
                    ) {
                        $address->setBaseOriginalAddress($originalAddress);
                        $address->setFormattedAddress($googleAddress->formatted_address);
                        $address->setPlaceId($googleAddress->place_id);
                        foreach ($googleAddress->address_components as $component) {
                            if (in_array('locality', $component->types)) {
                                $address->setCity($component->long_name);
                            }
                            if (in_array('street_number', $component->types)) {
                                $address->setStreetNumber($component->short_name);
                            }
                            if (in_array('route', $component->types)) {
                                $address->setStreet($component->long_name);
                            }
                            if (in_array('administrative_area_level_1', $component->types)) {
                                $address->setState($component->short_name);
                            }
                            if (in_array('postal_code', $component->types)) {
                                $address->setPostalCode($component->long_name);
                            }
                        }
                        $this->entityManager->persist($address);
                        $this->entityManager->flush();
                    }
                    $user->setAddress($address);

                    // Set user data
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
                }
                // Delete
                else {
                    $user->setEmail(sprintf('%s-%s-sync-deleted', $data['email'], strtotime('now')));
                    $user->setDeleted(1);
                    $this->countDelete++;
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        });

        $output->writeln(
            sprintf(
                'Finished sync. Created: %s. Updated: %s. Deleted %s.',
                $this->countCreate,
                $this->countUpdate,
                $this->countDelete
            )
        );
    }

    /**
     * @param $text
     * @return null or object
     */
    private function getAddressViaTextSearch($text)
    {
        $data = json_decode(file_get_contents(sprintf(
            self::GOOGLE_API.'textsearch/json?key=%s&query=%s',
            $this->app->getConfigByName('google', 'apiKey'),
            urlencode($text)
        )));

        if (!($data->status === 'OK' && isset($data->results[0]->place_id))) {
            return null;
        }

        $data = json_decode(file_get_contents(sprintf(
            self::GOOGLE_API.'details/json?key=%s&placeid=%s',
            $this->app->getConfigByName('google', 'apiKey'),
            $data->results[0]->place_id
        )));

        if ($data->status !== 'OK') {
            return null;
        }

        return $data->result;
    }
}
