<?php

namespace LO\Console\Command;

use Doctrine\ORM\EntityManager;
use LO\Model\Entity\Address;
use LO\Model\Entity\Lender;
use LO\Model\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersImportCommand extends Command {


    /**
     * @var EntityManager
     */
    private $entityManager;

    function __construct($entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('portal:import-users')
            ->setDescription('Import users from CSV file')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Please specify csv file to import from'
            )
//            ->addOption(
//                'sql',
//                null,
//                InputOption::VALUE_NONE,
//                'If set, the task will generate sql'
//            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');
        if (!file_exists($filename)) {
            $output->writeln("The file $filename does not exist");
            exit;
        }

        if (($handle = fopen($filename, "r")) === FALSE) {
            throw new \Exception(sprintf("File \'%s\' not found.", $filename));
        }

        $count = 0;
        while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
            $email = $data[6];
            if($email == 'Email') {
                continue;
            }

            $count += $this->hanldeUserFromData($data, $output);

            if($count > 50) {
                die();
            }
            //$text .= $email . '\n';
        }
        fclose($handle);
        $output->writeln("Total new users added: " . $count);
    }

    /**
     * return 0 if nothing added, return 1 if user was added
     *
     * @param array $data
     * @param OutputInterface $output
     * @return int
     */
    private function hanldeUserFromData(array $data, OutputInterface $output) {

        $email = $data[6];

        $user = $this->entityManager->getRepository(User::class)->findBy(array('email' => $email));

        if($user) {
            $output->writeln("Already added loan officer: $email");
            return 0;
        } else {
            $output->writeln("New loan officer: $email");
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($data[0]);
        $user->setLastName($data[1]);
        $user->setTitle($data[2]);
        $user->setPhone($data[5]);

        $salesDirector = $this->getSalesDirector($data[8]);
        if($salesDirector == null) {
            $output->writeln("Unknown sales director: $data[8]");
            die();
        }
        $user->setSalesDirector($salesDirector['name']);
        $user->setSalesDirectorEmail($salesDirector['email']);
        $user->setSalesDirectorPhone($salesDirector['phone']);

        $lender = $this->getLender($data[7]);
        if($lender == null) {
            $output->writeln("Unknown lender: $data[7]");
            die();
        }
        $user->setLender($lender);

        $user->setPassword('123456');
        $user->setSalt('6f8494e32b77d03dd13ae2fc3b4fb51f');
        $user->setState(1);
        $user->setRoles([User::ROLE_USER]);

        $mailingStreet = $data[3];
        $mailingCity = $data[4];
        $address = $this->getAddress($mailingCity);
        if($address == null) {
            $output->writeln("Unknown address: $mailingStreet, $mailingCity");
            die();
        } else {
            $addressObj = new Address();
            $addressObj->setFormattedAddress($address->description);
            $addressObj->setPlaceId($address->place_id);
            $addressObj->setCity($address->terms[0]->value);
            $addressObj->setState($address->terms[1]->value);
            $this->entityManager->persist($addressObj);
            $user->setAddress($addressObj);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return 1;
    }

    private function getSalesDirector($salesDirectorName) {

        if($salesDirectorName == 'Mike Lyon') {
            return array(
                'name' => 'Mike Lyon',
                'email' => 'mike.lyon@1rex.com',
                'phone' => '925-548-5157',
            );
        }

        if($salesDirectorName == 'Paul Careaga') {
            return array(
                'name' => 'Paul Careaga',
                'email' => 'paul.careaga@1rex.com',
                'phone' => '253-677-4470',
            );
        }

        if($salesDirectorName == 'Jim McGuire') {
            return array(
                'name' => 'Jim McGuire',
                'email' => 'jim.mcguire@1rex.com',
                'phone' => '310-909-6167',
            );
        }
        return null;
    }

    /**
     * @param $lenderName
     * @return Lender
     */
    private function getLender($lenderName) {
        $nameToSearch = $lenderName;
        if($lenderName == 'Cohen Financial Group') {
            $nameToSearch = 'Cohen Financial';
        } else if ($lenderName == 'MSP') {
            $nameToSearch = 'Mortgage Service Professionals';
        } else if ($lenderName == 'RPM') {
            $nameToSearch = "RPM Mortgage";
        } else if ($lenderName == 'WMS') {
            $nameToSearch = "Windermere Mortgage";
        }

        return $this->entityManager->getRepository(Lender::class)->findOneBy(array('name' => $nameToSearch));
    }

    private function getAddress($address) {
        $key = "AIzaSyC4lwX5bWIydtFeREY-T5bYT64q8s4WxTk";
        $query = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?key=$key&input=$query&types=geocode";
        $json = file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if($status == "OK") {
            //print_r($data->predictions[0]);
            return $data->predictions[0];
        }
        return null;
    }

    private function getAddressViaTextSearch($address) {
        $key = "AIzaSyC4lwX5bWIydtFeREY-T5bYT64q8s4WxTk";
        $query = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?key=$key&query=$query";
        $json = file_get_contents($url);
        $data = json_decode($json);
        var_dump($data);
        $status = $data->status;
        if($status == "OK"){
            print_r($data->results[0]);
             return $data->results[0]->formatted_address;
        }
        return '';
    }
} 