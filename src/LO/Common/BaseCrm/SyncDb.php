<?php
/**
 * User: Eugene Lysenko
 * Date: 1/13/16
 * Time: 19:42
 */

namespace LO\Common\BaseCrm;

use LO\Application,
    LO\Model\Entity\User,
    LO\Model\Entity\Lender,
    LO\Model\Entity\Address,
    \BaseCRM\Client,
    \BaseCRM\Sync,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException;

class SyncDb
{
    const GOOGLE_API      = 'https://maps.googleapis.com/maps/api/place/';

    private $syncAddress  = true;

    /**
     * Counters
     *
     * @var int
     */
    private $countUpdate  = 0;
    private $countCreate  = 0;
    private $countDelete  = 0;
    private $countErrors  = 0;

    private $app;
    private $em;
    private $sync;
    private $notLender;
    private $log          = [];

    function __construct(Application $app)
    {
        $this->app  = $app;
        $this->em   = $app->getEntityManager();
        $this->sync = new Sync(
            (new Client(['accessToken' => $this->app->getConfigByName('basecrm', 'accessToken')])),
            $this->app->getConfigByName('basecrm', 'devicesUuid')
        );
    }

    public function user()
    {
        // Sync contacts
        $this->sync->fetch(function($meta, $data) {
            if ($meta['type'] === 'contact' && isset($data['id']) && !empty($data['email'])) {
                try {
                    $user = $this->em->createQueryBuilder()
                        ->select('u')
                        ->from(User::class, 'u')
                        ->where('u.base_id = :id OR u.email = :email')->setParameters([
                            'id'    => $data['id'],
                            'email' => $data['email']
                        ])
                        ->getQuery()
                        ->getSingleResult();

                    // Error
                    if ($user->getBaseId() != $data['id']) {
                        $this->countErrors++;
                        $user->setEmail(sprintf('%s-%s-sync-error', $data['email'], strtotime('now')));
                        $user->setDeleted('1');
                        $this->add($user, $data);

                        return Sync::NACK;
                    }
                }
                catch (NonUniqueResultException $e) {
                    // Error
                    $this->countErrors++;
                    $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                    $user->setEmail(sprintf('%s-%s-sync-error', $data['email'], strtotime('now')));
                    $user->setDeleted('1');
                    $this->add($user, $data);

                    $this->app->getMonolog()->addError($e->getMessage());

                    return Sync::NACK;
                }
                catch (NoResultException $e) {
                    $user = new User;
                }

                $user->setEmail($data['email']);
                $this->add($user, $data);

                return Sync::ACK;
            }
        });

        try {
            $this->em->flush();
        }
        catch (\Exception $e) {
            $this->app->getMonolog()->addError($e->getMessage());
        }

        return [
            'create' => $this->countCreate,
            'update' => $this->countUpdate,
            'delete' => $this->countDelete,
            'errors' => $this->countErrors
        ];
    }

    /**
     * @param User $user
     * @param array $data
     * @return $this
     */
    private function add(User $user, array $data)
    {
        // Set lender data
        $lenderName = isset($data['custom_fields']['Sub-Company Name (DBA)'])
            ? $data['custom_fields']['Sub-Company Name (DBA)']
            : Lender::NOT_LENDER_NAME;
        if (!($lender = $this->em->getRepository(Lender::class)->findOneBy(['name' => $lenderName]))) {
            $lender = $this->getNotLender();
        }
        $user->setLender($lender);

        // Create
        if (!$user->getId()) {
            $this->countCreate++;
            $user->setBaseId($data['id']);
            $user->setPassword(User::DEFAULT_PASSWORD);
            $user->setRoles([User::ROLE_USER]);
        }

        // Update
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
            $this->countUpdate++;
            $user->setDeleted('0');
            $user = $this->fill($user, $data);
        }
        // Delete
        else {
            $this->countDelete++;
            $user->setEmail(sprintf('%s-%s-sync-deleted', $data['email'], strtotime('now')));
            $user->setDeleted('1');
        }

        if (!isset($this->log[$user->getEmail()])) {
            $this->log[$user->getEmail()] = [];
        }
        else {
            $this->countErrors++;
            $email = sprintf('%s-%s-sync-error', $user->getEmail(), strtotime('now'));
            $this->log[$email] = [];
            $user->setEmail($email);
            $user->setDeleted('1');
        }

        $this->em->persist($user);

        return $this;
    }

    /**
     * Get or create "Not Lender" company
     *
     * @return Lender
     */
    private function getNotLender()
    {
       if ($this->notLender) {
           return $this->notLender;
       }

        $this->notLender = $this->em->getRepository(Lender::class)->findOneBy(['name' => Lender::NOT_LENDER_NAME]);
        if ($this->notLender === null) {
            $this->notLender = new Lender;
            $this->notLender->setName(Lender::NOT_LENDER_NAME);
            $this->em->persist($this->notLender);
            $this->em->flush();
        }

        return $this->notLender;
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    private function fill(User $user, array $data)
    {
        // Set address data
        $address         = $user->getAddress() ? $user->getAddress() : new Address;
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
            $user->setAddress($address);
        }

        // Set user data
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setTitle($data['title']);
        $user->setPhone($data['phone']);
        $user->setMobile($data['mobile']);
        if (isset($data['custom_fields']['NMLS'])) {
            $user->setNmls($data['custom_fields']['NMLS']);
        }
        if (isset($data['custom_fields']['Sales Director'])) {
            $user->setSalesDirector($data['custom_fields']['Sales Director']);
        }
        if (isset($data['custom_fields']['Sales Director Phone Number'])) {
            $user->setSalesDirectorPhone($data['custom_fields']['Sales Director Phone Number']);
        }
        if (isset($data['custom_fields']['Sales Director Email'])) {
            $user->setSalesDirectorEmail($data['custom_fields']['Sales Director Email']);
        }

        return $user;
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
