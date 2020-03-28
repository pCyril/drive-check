<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Store;
use App\Form\Type\ActionType;
use App\Repository\ActionRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/dashboard", name="dashboard_")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $em)
    {
        /** @var ActionRepository $actionRepository */
        $actionRepository = $em->getRepository('App:Action');

        $query = $actionRepository->getActionByUserQuery($this->getUser());

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 's.store',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );

        return $this->render('dashboard/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/action/add/{action}", name="add_action")
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Action|null $action
     *
     * @return Response
     */
    public function actionForm(EntityManagerInterface $em, Request $request, Action $action = null)
    {
        $action = $action ?:new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'action' => $this->generateUrl('dashboard_add_action', ['action' => $action ? $action->getId() : null])
        ]);

        $form->get('store_company')->setData($action->getStore()->getStore());
        $form->get('storeId')->setData($action->getStore()->getStoreId());
        $form->get('storeName')->setData($action->getStore()->getStoreName());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $company = $form->get("store_company")->getData();
            $storeId = $form->get("storeId")->getData();
            $storeName = $form->get("storeName")->getData();

            /** @var StoreRepository $storeRepository */
            $storeRepository = $em->getRepository('App:Store');

            /** @var Store $store */
            $store = $storeRepository->findOneBy(['store' => $company, 'storeId' => $storeId]);

            if (!$store) {
                $store = (new Store())
                    ->setStore($company)
                    ->setStoreId($storeId)
                    ->setStoreName($storeName);
            } else {
                if (!$store->getStoreName()) {
                    $store->setStoreName($storeName);
                }
            }

            $em->persist($store);

            $action
                ->setStore($store)
                ->setUser($this->getUser());
            $em->persist($action);
            $em->flush();

            return new Response(null);
        }

        return $this->render('admin/forms/defaultForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/action/{action}", name="delete_action")
     *
     * @param EntityManagerInterface $em
     * @param Action $action
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(EntityManagerInterface $em, Action $action)
    {
        if ($this->getUser()->getId() !== $action->getUser()->getId()) {
            return $this->redirectToRoute('dashboard_home');
        }

        $em->remove($action);
        $em->flush();

        return $this->redirectToRoute('dashboard_home');
    }

    /**
     * @Route("/follow/store/{store}", name="follow_store")
     *
     * @param EntityManagerInterface $em
     * @param Store $store
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function followStore(EntityManagerInterface $em, Store $store)
    {
        $userAction = $em->getRepository('App:Action')->findOneBy(['store' => $store, 'user' => $this->getUser()]);

        if (!$userAction) {
            $newAction = (new Action())
                ->setStore($store)
                ->setUser($this->getUser());

            $em->persist($newAction);
            $em->flush();
        } else {

            return $this->redirectToRoute('dashboard_stores');
        }

        return $this->redirectToRoute('dashboard_home');
    }

    /**
     * @Route("/stores", name="stores")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function stores(Request $request, PaginatorInterface $paginator, EntityManagerInterface $em)
    {
        /** @var StoreRepository $actionRepository */
        $storeRepository = $em->getRepository('App:Store');

        $query = $storeRepository->getStores();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 's.store',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );

        return $this->render('dashboard/stores.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    

    /**
     * @Route("/store/{store}", name="store")
     *
     * @param EntityManagerInterface $em
     * @param Store $store
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function store(EntityManagerInterface $em, Store $store)
    {
        $slots = $em->getRepository('App:Slot')->getSlotsLastTwentyFourHours();
        $slotsJsSeries = [];
        $slotsJsLabels = [];
        $isOpenSlot = 0;
        $hour = '';
        foreach($slots as $slot){
            if($slot->getCreatedAt()->format('H') != $hour){
                $hour = $slot->getCreatedAt()->format('H');
                $slotsJsLabels[] = $slot->getCreatedAt()->format('H:i');
            }else{
                $slotsJsLabels[] = '';
            }
            $slotsJsSeries[] = $slot->isOpen();
            if ($slot->isOpen()) {
                $isOpenSlot++;        
            }
        }
        $isCloseSlot = count($slots) - $isOpenSlot;
        $slotJsDonutSeries = [$isOpenSlot, $isCloseSlot];

        return $this->render('dashboard/store.html.twig', [
            'slotsJsLabels' => json_encode($slotsJsLabels),
            'slotsJsSeries' => json_encode($slotsJsSeries),
            'slotJsDonutSeries' => json_encode($slotJsDonutSeries)
        ]);
    }
}
