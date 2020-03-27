<?php

namespace App\Command;

use App\Entity\Action;
use App\Entity\ProductAlert;
use App\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckProductCommand extends Command
{
    private $em;
    private $mailer;
    private $templating;
    private $stocks = [];

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
            ->setName('drive:check:product');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productAlert = $this->em->getRepository('App:ProductAlert');

        $productAlerts = $productAlert->findAll();

        $progressBar = new ProgressBar($output);

        /**
         * @var  $key
         * @var ProductAlert $productAlert
         */
        foreach ($progressBar->iterate($productAlerts) as $key => $productAlert) {
            switch ($productAlert->getStore()->getStore()) {
                case 'auchan':
                    $productAlert = $this->checkAuchanStock($productAlert);
                    break;
                default:
                    break;
            }

            $productAlert->setLastCheck(new \DateTime());
            $this->em->persist($productAlert);
            $this->em->flush();
        }
    }

    public function checkAuchanStock(ProductAlert $productAlert)
    {
        $store = $productAlert->getStore();

        if (!array_key_exists($store->getStoreId(), $this->stocks)) {
            $client = new Client();

            $response = $client->request('GET', 'https://mobile.auchandrive.fr/stockout/STOCKOUT_' . $store->getStoreId());

            $result = json_decode($response->getBody()->__toString(), true);

            foreach ($result['cugs'] as $row) {
                foreach ($row as $key => $value) {

                    $this->stocks[$store->getStoreId()][$key] = 0;
                }
            }
        }


        if (array_key_exists($productAlert->getProductId(), $this->stocks[$store->getStoreId()])) {
            if (!$productAlert->isStockOut()) {
                $productAlert->setStockOut(true);
                $this->sendEmail($productAlert, true);
            }
        } else {
            if ($productAlert->isStockOut()) {
                $this->sendEmail($productAlert, false);
                $productAlert->setStockOut(false);
            }
        }

        return $productAlert;
    }

    /**
     * @param ProductAlert $productAlert
     */
    public function sendEmail(ProductAlert $productAlert, $out = true)
    {
        $subject = 'Produit disponible';
        $template = 'mail/stock_in.html.twig';

        if ($out) {
            $subject = 'Produit indisponible';
            $template = 'mail/stock_out.html.twig';
        }

        $store = $productAlert->getStore();

        $message = (new \Swift_Message($subject))
            ->setFrom('perraud.cyril@gmail.com')
            ->setTo($productAlert->getUser()->getEmail())
            ->setBody(
                $this->renderHTML($this->templating->render(
                    $template,
                    [
                        'store' => $store->getStore(),
                        'id' => $store->getStoreId(),
                        'storeName' => $store->getStoreName(),
                        'productName' => $productAlert->getProductName(),
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
