<?php
/**
 * User: Eugene Lysenko
 * Date: 2/17/16
 * Time: 12:23
 */

namespace LO\Common\BaseCrm;

use LO\Application,
    LO\Model\Entity\User,
    \BaseCRM\Client,
    \BaseCRM\Errors\RequestError;

class UploadDb
{
    /**
     * Variables
     *
     * @var
     */
    private $app;
    private $em;
    private $client;

    /**
     * Counters
     *
     * @var int
     */
    private $countUpdate   = 0;
    private $countNotFound = 0;

    /**
     * @param Application $app
     */
    function __construct(Application $app)
    {
        $this->app    = $app;
        $this->em     = $app->getEntityManager();
        $this->client = new Client(['accessToken' => $app->getConfigByName('basecrm', 'accessToken')]);
    }

    /**
     * @return array
     */
    public function contacts()
    {
        $users = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            if (empty($user->getBaseId())) {
                continue;
            }

            // Sync contacts
            try {
                $this->client->contacts->update(
                    $user->getBaseId(),
                    ['custom_fields' => ['Portal Password' => $user->getPassword()]]
                );
                $this->countUpdate++;
            }
            catch (RequestError $e) {
                $this->countNotFound++;
            }
        }

        return [
            'update'    => $this->countUpdate,
            'not_found' => $this->countNotFound
        ];
    }
}
