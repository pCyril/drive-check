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

        $actions = $actionRepository->findAll();

        $progressBar = new ProgressBar($output);

        /**
         * @var  $key
         * @var Action $action
         */
        foreach ($progressBar->iterate($actions) as $key => $action) {
            switch ($action->getStore()) {
                case 'auchan':
                    $this->checkAuchanDrive($action);
                    break;
                case 'carrefour':
                    $this->checkCarrefourDrive($action);
                    break;
                case 'super_u':
                    $this->checkSuperUDrive($action);
                    break;
            }
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

        if ($result['status'] === 'ok') {
            $this->sendEmail($action);
        }
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

        if (!empty($result)) {
            $this->sendEmail($action);
        }
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

        if ($availableSlot) {
            $this->sendEmail($action);
        }
    }

    /**
     * @param Action $action
     */
    public function sendEmail(Action $action)
    {
        $message = (new \Swift_Message('Votre drive a un créneau disponible'))
            ->setFrom('perraud.cyril@gmail.com')
            ->setTo($action->getUser()->getEmail())
            ->setBody(
                $this->renderHTML($this->templating->render(
                // templates/emails/registration.html.twig
                    'mail/new_slot.html.twig',
                    [
                        'store' => $action->getStore(),
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
