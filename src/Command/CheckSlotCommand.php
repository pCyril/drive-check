<?php

namespace App\Command;

use App\Entity\Action;
use App\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckSlotCommand extends Command
{
    private $em;
    private $mailer;
    private $templating;

    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $container->get('templating');

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('drive:check:slot');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storeRepository = $this->em->getRepository('App:Store');

        $stores = $storeRepository->findAll();

        $progressBar = new ProgressBar($output);

        /**
         * @var  $key
         * @var Store $store
         */
        foreach ($progressBar->iterate($stores) as $key => $store) {
            switch ($store->getStore()) {
                case 'auchan':
                    $store = $this->checkAuchanDrive($store);
                    break;
                case 'carrefour':
                    $store = $this->checkCarrefourDrive($store);
                    break;
                case 'super_u':
                    $store = $this->checkSuperUDrive($store);
                    break;
            }

            $store->setLastCheck(new \DateTime());
            $this->em->persist($store);
            $this->em->flush();
        }
    }

    public function checkAuchanDrive(Store $store)
    {
        $client = new Client();

        $response = $client->request('POST', 'https://mobile.auchandrive.fr/ws/NextSlot', [
            'form_params' => [
                'shopId' => $store->getStoreId(),
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);

        if ($result['status'] === 'ok' && !$store->isSlotOpen()) {
            $store->setSlotOpen(true);
            $this->sendEmail($store);
        } else {
            if ($store->isSlotOpen()) {
                $this->sendEmail($store, true);
            }
            $store->setSlotOpen(false);
        }

        return $store;
    }

    public function checkCarrefourDrive(Store $store)
    {
        $client = new Client();

        $response = $client->request('GET', 'https://www.carrefour.fr/api/firstslot?storeId=' . $store->getStoreId(), [
            'headers' => [
                'x-requested-with' => 'XMLHttpRequest'
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);

        if (!empty($result) && !$store->isSlotOpen()) {
            $store->setSlotOpen(true);
            $this->sendEmail($store);
        } else {
            if ($store->isSlotOpen()) {
                $this->sendEmail($store, true);
            }
            $store->setSlotOpen(false);
        }

        return $store;
    }

    public function checkSuperUDrive(Store $store)
    {
        $client = new Client();

        $client->request('POST', 'https://www.coursesu.com/on/demandware.store/Sites-DigitalU-Site/fr_FR/Stores-SetStoreWeb?format=ajax', [
            'form_params' => [
                'storeId' => $store->getStoreId(),
                'partnerStoreId' => '',
            ],
            'headers' => [
                'Cookie' => 'dwsid=sgTEy-i7OMSQXQhBLFZWJhg6y5btYdgbOgi0G-MKL3MWOjuWJ2Dlg2Wqn-1WIQ_W5-x9xhxVJ9hpSNGuRkdn_A==',
            ]
        ]);


        $response = $client->request('POST', 'https://www.coursesu.com/on/demandware.store/Sites-DigitalU-Site/fr_FR/DeliverySlot-GetDeliverySlots', [
            'form_params' => [
                'deliveryPoint' => 'drive',
            ],
            'headers' => [
                'Cookie' => 'dwsid=sgTEy-i7OMSQXQhBLFZWJhg6y5btYdgbOgi0G-MKL3MWOjuWJ2Dlg2Wqn-1WIQ_W5-x9xhxVJ9hpSNGuRkdn_A==; storeId='.$store->getStoreId().';'
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);

        $availableSlot = false;
        foreach ($result['slotList'] as $slot) {
            if ($slot['capacityMax'] != $slot['capacityUsed']) {
                $availableSlot = true;
                break;
            }
        }

        if ($availableSlot && !$store->isSlotOpen()) {
            $store->setSlotOpen(true);
            $this->sendEmail($store);
        } else {
            if ($store->isSlotOpen()) {
                $this->sendEmail($store, true);
            }
            $store->setSlotOpen(false);
        }

        return $store;
    }

    /**
     * @param Store $store
     */
    public function sendEmail(Store $store, $close = false)
    {
        $actionRepository = $this->em->getRepository('App:Action');

        $actions = $actionRepository->findBy(['onBreak' => false, 'sotre' => $store]);

        /** @var Action $action */
        foreach ($actions as $action) {
            $subject = 'Votre drive a un créneau disponible';
            $template = 'mail/new_slot.html.twig';

            if ($close) {
                $subject = 'Votre drive n\'a plus de créneaux disponibles';
                $template = 'mail/close_slot.html.twig';
            }

            $message = (new \Swift_Message($subject))
                ->setFrom('perraud.cyril@gmail.com')
                ->setTo($action->getUser()->getEmail())
                ->setBody(
                    $this->renderHTML($this->templating->render(
                        $template,
                        [
                            'store' => $action->getStore()->getStore(),
                            'id' => $action->getStore()->getStoreId(),
                            'storeName' => $action->getStore()->getStoreName(),
                        ]
                    )),
                    'text/html'
                )
            ;

            $this->mailer->send($message);
        }

    }

    public function renderHTML($mjml) {

        $client = new Client([
            'base_uri' => 'http://tika.mycacorsica.fr/',
        ]);

        $response = $client->request('POST', 'mjml.php', [
            'form_params' => ['mjml' => $mjml],
        ]);

        return $response->getBody();
    }
}
