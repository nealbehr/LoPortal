<?php
/**
 * User: Eugene Lysenko
 * Date: 1/13/16
 * Time: 19:42
 */
namespace LO\Common\BaseCrm;

use LO\Application;
use LO\Model\Entity\User;
use LO\Model\Entity\Lender;
use LO\Model\Entity\Address;
use \BaseCRM\Client;
use \BaseCRM\Sync;
use Doctrine\ORM\NoResultException;

class SyncDb
{
    const DEFAULT_PASSWORD = '123456';
    const GOOGLE_API       = 'https://maps.googleapis.com/maps/api/place/';

    const NOT_LENDER_NAME  = 'Not Lender';

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
        $this->app           = $app;
        $this->entityManager = $app->getEntityManager();
        $this->sync          = new Sync(
            (new Client(['accessToken' => $this->app->getConfigByName('basecrm', 'accessToken')])),
            $this->app->getConfigByName('basecrm', 'devicesUuid')
        );
    }

    public function user()
    {
        $qUser   = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')->where('u.base_id = :id');
        $qLender = $this->entityManager->getRepository(Lender::class)->createQueryBuilder('l')->where('l.name = :name');

        // Get or create "Not Lender" company
        try {
            $notLender = $qLender->setParameter('name', self::NOT_LENDER_NAME)->getQuery()->getSingleResult();
        }
        catch (NoResultException $e) {
            $notLender = new Lender;
            $notLender->setName(self::NOT_LENDER_NAME);
            $this->entityManager->persist($notLender);
            $this->entityManager->flush();
        }

        $this->sync->fetch(function($meta, $data) use ($qUser, $qLender, $notLender) {
            // Sync contacts
            if ($meta['type'] === 'contact' && isset($data['id']) && !empty($data['email'])) {
                // Update user
                try {
                    $user    = $qUser->setParameter('id', $data['id'])->getQuery()->getSingleResult();
                    $address = $user->getAddress();
                    $user->setDeleted('0');
                    $user->setUpdatedAt(null);
                    $this->countUpdate++;
                }
                // Create user
                catch (NoResultException $e) {
                    $user    = new User;
                    $address = new Address;
                    $user->setBaseId($data['id']);
                    $user->setPassword(self::DEFAULT_PASSWORD);
                    $user->setRoles([User::ROLE_USER]);
                    $this->countCreate++;
                }
                $originalAddress = implode(', ', $data['address']);
                $address->setBaseOriginalAddress($originalAddress);

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
                    $user->setUpdatedAt(null);
                    $user->setDeleted('1');
                    $this->countDelete++;
                }

                try {
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }
                catch (\Exception $e) {
                    $this->app->getMonolog()->addError($e->getMessage());
                }

                return Sync::ACK;
            }
        });

        return [
            'create' => $this->countCreate,
            'update' => $this->countUpdate,
            'delete' => $this->countDelete
        ];
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
