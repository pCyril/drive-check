<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\PBN;
use App\Entity\Thematic;
use App\Entity\User;
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
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{

    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/users/{role}", name="users")
     *
     * @param Request $request
     * @param string $role
     * @param PaginatorInterface $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function users(Request $request, PaginatorInterface $paginator, string $role = 'ROLE_USER')
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getManager()->getRepository('App:User');

        $query = $userRepository->getByRoleQuery($role);

        $pageTitle = 'Liste des utilisateus·rices';

        if ($role === 'ROLE_ADMIN') {
            $pageTitle = 'Liste des administrateurs·rice';
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            50,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'u.username',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );

        return $this->render('admin/users.html.twig', [
            'page_title' => $pageTitle,
            'pagination' => $pagination,
            'role' => $role,
        ]);
    }

    /**
     * @Route("/user/add/{user}", name="add_user")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param User|null $user
     *
     * @return Response
     */
    public function userForm(Request $request, User $user = null)
    {
        $builder = $this->createFormBuilder();

        $builder
            ->setAction($this->generateUrl('admin_add_user', ['user' => $user ? $user->getId() : null]))
            ->add('username', TextType::class, [
                'label' => 'Login',
                'required'   => true,
                'data' => $user ? $user->getUsername():null,
                'disabled' => !!$user,
            ])->add('email', TextType::class, [
                'label' => 'Email',
                'required'   => true,
                'data' => $user ? $user->getEmail():null,
            ])->add('password', TextType::class, [
                'label' => 'Mot de passe',
                'required'   => true,
            ])->add('type', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'data' => ($user && $user->hasRole('ROLE_ADMIN')) ? 'ROLE_ADMIN' : 'ROLE_USER',
            ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($user) {
                foreach ($user->getRoles() as $role) {
                    $user->removeRole($role);
                }

                $user->setEmail($data['email'])
                    ->setPlainPassword($data['password'])
                    ->addRole($data['type']);

                $this->userManager->updateUser($user);
            } else {
                $user = $this->userManager->createUser();

                $user->setUsername($data['username'])
                    ->setEmail($data['email'])
                    ->setEnabled(true)
                    ->setPlainPassword($data['password'])
                    ->addRole($data['type']);

                $this->userManager->updateUser($user);
            }

            return new Response(null);
        }

        return $this->render('admin/forms/defaultForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/users/{user}", name="delete_user")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param EntityManagerInterface $em
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUser(EntityManagerInterface $em, User $user)
    {
        $param = 'ROLE_USER';

        if ($user->hasRole('ROLE_ADMIN')) {
            $param = 'ROLE_ADMIN';
        }

        $user->setEnabled(false);
        $em->flush();

        return $this->redirectToRoute('admin_users', ['role' => $param]);
    }
}
