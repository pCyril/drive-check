<?php

namespace App\Command;

use App\Entity\Action;
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
        $actionRepository = $this->em->getRepository('App:Action');

        $actions = $actionRepository->findBy(['onBreak' => false]);

        $progressBar = new ProgressBar($output);

        /**
         * @var  $key
         * @var Action $action
         */
        foreach ($progressBar->iterate($actions) as $key => $action) {
            switch ($action->getStore()) {
                case 'auchan':
                    $action = $this->checkAuchanDrive($action);
                    break;
                case 'carrefour':
                    $action = $this->checkCarrefourDrive($action);
                    break;
                case 'super_u':
                    $action = $this->checkSuperUDrive($action);
                    break;
            }

            $action->setLastCheck(new \DateTime());
            $this->em->persist($action);
            $this->em->flush();
        }
    }

    public function checkAuchanDrive(Action $action)
    {
        $client = new Client();

        $response = $client->request('POST', 'https://mobile.auchandrive.fr/ws/NextSlot', [
            'form_params' => [
                'shopId' => $action->getStoreId(),
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);

        if ($result['status'] === 'ok' && !$action->isSlotOpen()) {
            $action->setSlotOpen(true);
            $this->sendEmail($action);
        } else {
            if ($action->isSlotOpen()) {
                $this->sendEmail($action, true);
            }
            $action->setSlotOpen(false);
        }

        return $action;
    }

    public function checkCarrefourDrive(Action $action)
    {
        $client = new Client();

        $response = $client->request('GET', 'https://www.carrefour.fr/api/firstslot?storeId=' . $action->getStoreId(), [
            'headers' => [
                'x-requested-with' => 'XMLHttpRequest'
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);

        if (!empty($result) && !$action->isSlotOpen()) {
            $action->setSlotOpen(true);
            $this->sendEmail($action);
        } else {
            if ($action->isSlotOpen()) {
                $this->sendEmail($action, true);
            }
            $action->setSlotOpen(false);
        }

        return $action;
    }

    public function checkSuperUDrive(Action $action)
    {
        $client = new Client();

        $response = $client->request('POST', 'https://www.coursesu.com/on/demandware.store/Sites-DigitalU-Site/fr_FR/DeliverySlot-GetDeliverySlots', [
            'form_params' => [
                'deliveryPoint' => 'drive',
            ],
            'headers' => [
                'Cookie' => 'dwsid=B53OlSsoxHiLgV_Kj_DW18VlLBDwKc2A4KOP3JlQvJwPdpZI2gNlr8huwMZ-qVzqfrMIg8sShATXB61omx62ZA==; storeId='.$action->getStoreId().';'
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

        if ($availableSlot && !$action->isSlotOpen()) {
            $action->setSlotOpen(true);
            $this->sendEmail($action);
        } else {
            if ($action->isSlotOpen()) {
                $this->sendEmail($action, true);
            }
            $action->setSlotOpen(false);
        }

        return $action;
    }

    /**
     * @param Action $action
     */
    public function sendEmail(Action $action, $close = false)
    {
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
                        'store' => $action->getStore(),
                        'id' => $action->getStoreId(),
                        'storeName' => $action->getStoreName(),
                    ]
                )),
                'text/html'
            )
        ;

        $this->mailer->send($message);
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
