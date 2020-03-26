<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Client;
use App\Entity\PBN;
use App\Entity\Thematic;
use App\Entity\User;
use App\Form\Type\ActionType;
use App\Form\Type\ClientType;
use App\Form\Type\PBNType;
use App\Form\Type\ThematicType;
use App\Repository\ClientRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
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
        $actionRepository = $em->getRepository('App:Action');

        $query = $actionRepository->getActionByUserQuery($this->getUser());

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'a.store',
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

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action->setUser($this->getUser());
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
}
