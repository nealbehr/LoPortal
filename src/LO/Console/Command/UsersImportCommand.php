<?php

namespace LO\Console\Command;

use Doctrine\ORM\EntityManager;
use LO\Model\Entity\Address;
use LO\Model\Entity\Lender;
use LO\Model\Entity\User;
use SpreadsheetReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersImportCommand extends Command
{


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

        $count = 0;
        $reader = new SpreadsheetReader($filename);
        $sheets = $reader ->Sheets();

        foreach ($sheets as $index => $name) {
            $reader ->ChangeSheet($index);

            foreach ($reader as $row) {
                if($row[0] == 'First Name') {
                    // ignore titles
                    continue;
                }
                $count += $this->handleUpdates($row, $output);

                //test on one
                if($count > 1000) {
                    break;
                }
            }
        }

        $output->writeln("Total users updated: " . $count);
    }

    /**
     * return 0 if nothing added, return 1 if user was added
     *
     * @param array $data
     * @param OutputInterface $output
     * @return int
     */
    private function handleUserFromData(array $data, OutputInterface $output)
    {

        $email = $data[6];

        $user = $this->entityManager->getRepository(User::class)->findBy(array('email' => $email));

        if ($user) {
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
        if ($salesDirector == null) {
            $output->writeln("Unknown sales director: $data[8]");
            die();
        }
        $user->setSalesDirector($salesDirector['name']);
        $user->setSalesDirectorEmail($salesDirector['email']);
        $user->setSalesDirectorPhone($salesDirector['phone']);

        $lender = $this->getLender($data[7]);
        if ($lender == null) {
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
        if ($address == null) {
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

    private function handleUpdates(array $data, OutputInterface $output)
    {
        $firstName = $data[0];
        $lastName = $data[1];
        $title = $data[2];
        $nmls = $data[3];
        $mailingStreet = $data[4];
        $mailingCity = $data[5];
        $mailingState = $data[6];
        $mailingZip = $data[7];
        $mailingCountry = $data[8];
        $phone = $data[9];
        $fax = $data[10];
        $email = strtolower($data[11]);
        $accountName = $data[12];
        $territory = $data[13];
        $salesDirector = $data[14];

        $user = $this->entityManager->getRepository(User::class)->findOneBy(array('email' => $email));
        /* @var User $user */
        if (!$user) {
            $user = new User();
            $user->setPassword('123456');
            $user->setSalt('6f8494e32b77d03dd13ae2fc3b4fb51f');
            $user->setState(1);
            $user->setRoles([User::ROLE_USER]);

//            $output->writeln("Can't find loan officer: $email");
//            return 0;
        }

        $salesDirector = $this->getSalesDirector($salesDirector);
        if ($salesDirector == null) {
            $output->writeln("Unknown sales director: $salesDirector");
            die();
        }
        $user->setSalesDirector($salesDirector['name']);
        $user->setSalesDirectorEmail($salesDirector['email']);
        $user->setSalesDirectorPhone($salesDirector['phone']);

        $lender = $this->getLender($accountName);
        if ($lender == null) {
            $output->writeln("Unknown lender: $accountName");
            die();
        }
        $user->setLender($lender);

        $output->writeln("Updating loan officer: $email");
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setTitle($title);
        $user->setNmls($nmls);
        $user->setEmail($email);
        $user->setPhone($phone);

        // update address if only don't have zip
        if($user->getAddress()->getPostalCode() == null) {
            $address = $this->getAddressViaTextSearch($mailingStreet . ', ' . $mailingCity);
            if($address == null) {
                $output->writeln("Unknown address: $mailingStreet, $mailingCity");
                die();
            } else {
                // print_r($address);
                $addressObj = $user->getAddress();
                $addressObj->setFormattedAddress($address->formatted_address);
                $addressObj->setPlaceId($address->place_id);

                foreach ($address->address_components as $component) {

                    if(in_array('locality', $component->types)) {
                        $addressObj->setCity($component->long_name);
                    }
                    if(in_array('street_number', $component->types)) {
                        $addressObj->setStreetNumber($component->short_name);
                    }
                    if(in_array('route', $component->types)) {
                        $addressObj->setStreet($component->long_name);
                    }
                    if(in_array('administrative_area_level_1', $component->types)) {
                        $addressObj->setState($component->short_name);
                    }
                    if(in_array('postal_code', $component->types)) {
                        $addressObj->setPostalCode($component->long_name);
                    }
                }
                if($addressObj->getId() == null) {
                    $this->entityManager->persist($addressObj);
                } else {
                    $this->entityManager->merge($addressObj);
                }
                $this->entityManager->flush($addressObj);
                $user->setAddress($addressObj);
            }
        }

        if($user->getId() == null) {
            $this->entityManager->persist($user);
        } else {
            $this->entityManager->merge($user);
        }
        $this->entityManager->flush($user);
        return 1;
    }

    private function getSalesDirector($salesDirectorName)
    {

        if ($salesDirectorName == 'Mike Lyon') {
            return array(
                'name' => 'Mike Lyon',
                'email' => 'mike.lyon@1rex.com',
                'phone' => '925-548-5157',
            );
        }

        if ($salesDirectorName == 'Paul Careaga') {
            return array(
                'name' => 'Paul Careaga',
                'email' => 'paul.careaga@1rex.com',
                'phone' => '253-677-4470',
            );
        }

        if ($salesDirectorName == 'Jim McGuire') {
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
    private function getLender($lenderName)
    {
        $nameToSearch = $lenderName;
        if ($lenderName == 'Cohen Financial Group') {
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

    private function getAddress($address)
    {
        // $key = "AIzaSyC4lwX5bWIydtFeREY-T5bYT64q8s4WxTk"; local
        $key = "AIzaSyAWiNe3wNoy0kh1WOVgHcPGPJ65ukBwBu0";
        $query = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?key=$key&input=$query&types=geocode";
        $json = file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            //print_r($data->predictions[0]);
            return $data->predictions[0];
        }
        return null;
    }

    private function getAddressViaTextSearch($address)
    {
        // $key = "AIzaSyC4lwX5bWIydtFeREY-T5bYT64q8s4WxTk"; // stage

        $key = "AIzaSyDx9sAa1Zt05W3HPf0P5XTCZnnF_WOOToA";

        $query = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?key=$key&query=$query";
        $json = file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            $placeId = $data->results[0]->place_id;
            $pdUrl = "https://maps.googleapis.com/maps/api/place/details/json?key=$key&placeid=$placeId";
            $pdJson = file_get_contents($pdUrl);
            $response = json_decode($pdJson);
            if(!$response->result) {
                print_r($response);
            }
            return $response->result;
        }
        print_r($data);
        return null;
    }
} 