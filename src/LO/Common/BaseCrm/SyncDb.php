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
    LO\Common\UploadS3\File,
    \BaseCRM\Client,
    \BaseCRM\Sync,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException;

class SyncDb
{
    const GOOGLE_API     = 'https://maps.googleapis.com/maps/api/place/';

    private $syncAddress = true;

    /**
     * Counters
     *
     * @var int
     */
    private $countUpdate = 0;
    private $countCreate = 0;
    private $countDelete = 0;
    private $countErrors = 0;

    /**
     * Variables
     *
     * @var
     */
    private $app;
    private $em;
    private $sync;
    private $notLender;
    private $log         = [];

    /**
     * @param Application $app
     */
    function __construct(Application $app)
    {
        $this->app  = $app;
        $this->em   = $app->getEntityManager();
        $this->sync = new Sync(
            (new Client(['accessToken' => $this->app->getConfigByName('basecrm', 'accessToken')])),
            $this->app->getConfigByName('basecrm', 'devicesUuid')
        );
    }

    /**
     * @return array
     */
    public function user()
    {
        // Sync contacts
        $this->sync->fetch(function($meta, $data) {
            if ($meta['type'] === 'contact' && isset($data['id']) && !empty($data['email'])) {
                $notDelete = (
                    (
                        isset($data['custom_fields']['Active PMP'])
                        && 'yes' === strtolower($data['custom_fields']['Active PMP'])
                    )
                    || (
                        isset($data['custom_fields']['Sub-Company Name (DBA)'])
                        && 'firstrex' === strtolower($data['custom_fields']['Sub-Company Name (DBA)'])
                    )
                );

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

                    // Duplicate email address
                    if ($user->getBaseId() != $data['id']) {
                        $this->add($user, $data, 'duplicate');

                        return Sync::NACK;
                    }

                    $this->add($user, $data, ($notDelete ? 'update' : 'update_delete'));

                    return Sync::ACK;
                }
                catch (NonUniqueResultException $e) {
                    // Duplicate email address
                    $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                    $this->add($user, $data, 'duplicate');

                    $this->app->getMonolog()->addError($e->getMessage());

                    return Sync::NACK;
                }
                catch (NoResultException $e) {
                    $this->add(new User, $data, ($notDelete ? 'create' : 'create_delete'));

                    return Sync::ACK;
                }
            }
        });

        // Save date
        try {
            $this->em->flush();
        }
        catch (\Exception $e) {
            $this->app->getMonolog()->addError($e->getMessage());
        }

        // Save log
        $file = (
            new File($this->app->getS3(),
            $this->createCsvBase64($this->log),
            '1rex/sync/log',
            ['text/plain' => 'csv'])
        )->download('fulllog-'.strtotime('now'));

        return [
            'up_today'  => empty($this->log),
            'short_log' => [
                'create' => $this->countCreate,
                'update' => $this->countUpdate,
                'delete' => $this->countDelete,
                'errors' => $this->countErrors,
            ],
            'full_log'  => $file,
        ];
    }

    /**
     * Add for saving
     *
     * @param User $user
     * @param array $data
     * @return $this
     */
    private function add(User $user, array $data, $event)
    {
        switch ($event) {
            case 'create';
                $this->countCreate++;
                $user->setEmail($data['email']);
                $user->setBaseId($data['id']);
                $user->setPassword(User::DEFAULT_PASSWORD);
                $user->setRoles([User::ROLE_USER]);
                $user = $this->fill($user, $data);
                break;
            case 'create_delete';
                $this->countDelete++;
                $user->setEmail(sprintf('%s-%s-sync-deleted', $data['email'], strtotime('now')));
                $user->setDeleted('1');
                $user->setBaseId($data['id']);
                $user->setPassword(User::DEFAULT_PASSWORD);
                $user->setRoles([User::ROLE_USER]);
                break;
            case 'update':
                $this->countUpdate++;
                $user->setEmail($data['email']);
                $user->setDeleted('0');
                $user = $this->fill($user, $data);
                break;
            case 'update_delete':
                $this->countDelete++;
                $user->setEmail(sprintf('%s-%s-sync-deleted', $data['email'], strtotime('now')));
                $user->setDeleted('1');
                break;
            case 'duplicate':
                $this->countErrors++;
                $user->setEmail(sprintf('%s-%s-sync-error', $data['email'], strtotime('now')));
                $user->setDeleted('1');
                break;
        }

        // Set lender data
        $lenderName = isset($data['custom_fields']['Sub-Company Name (DBA)'])
            ? $data['custom_fields']['Sub-Company Name (DBA)']
            : Lender::NOT_LENDER_NAME;
        if (!($lender = $this->em->getRepository(Lender::class)->findOneBy(['name' => $lenderName]))) {
            $lender = $this->getNotLender();
        }
        $user->setLender($lender);

        // Duplicate email address
        if (isset($this->log[$user->getEmail()])) {
            $this->countErrors++;
            $event = 'duplicate';
            $email = sprintf('%s-%s-sync-error', $data['email'], strtotime('now'));
            $user->setEmail($email);
            $user->setDeleted('1');
        }

        // Sync log
        $this->log[$user->getEmail()] = [$data['id'], $event, $data['email'], $user->getEmail()];

        $this->em->persist($user);

        return $this;
    }

    /**
     * Fill user model
     *
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
     * Get "Not Lender" company
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
     * @param array $array
     * @param array $headers
     * @return bool|string
     */
    private function createCsvBase64(array $array, array $headers = ['Id', 'Event', 'Original email', 'New email'])
    {
        if (!($fp = fopen('php://filter/read=convert.base64-encode/resource=php://temp', 'w'))) {
            return false;
        }
        fputcsv($fp, $headers);
        foreach ($array as $line) {
            fputcsv($fp, $line);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return 'data:text/csv;base64,'.$csv;
    }

    /**
     * Get address by text string
     *
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
