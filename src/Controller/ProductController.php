<?php

namespace App\Controller;

use App\Entity\ProductAlert;
use App\Form\Type\ProductAlertEditType;
use App\Form\Type\ProductAlertType;
use App\Repository\ProductAlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/dashboard", name="dashboard_")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/missing/product", name="missing_product")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function missingProduct(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        /** @var ProductAlertRepository $productAlertRepository */
        $productAlertRepository = $em->getRepository('App:ProductAlert');

        $query = $productAlertRepository->getProductAlertsByUserQuery($this->getUser());

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 's.store',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/product/add/{productAlert}", name="add_product")
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ProductAlert $productAlert
     *
     * @return Response
     */
    public function addProductForm(EntityManagerInterface $em, Request $request, ProductAlert $productAlert = null)
    {
        $productAlert = $productAlert ?:new ProductAlert();
        $form = $this->createForm(ProductAlertType::class, $productAlert, [
            'action' => $this->generateUrl('dashboard_add_product', ['productAlert' => $productAlert ? $productAlert->getId() : null])
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productAlertRepository = $em->getRepository('App:ProductAlert');
            $productUrl = $form->get("product_url")->getData();

            preg_match('#([a-z\-1-9]+)-P([0-9]+)$#', $productUrl, $matches);

            if (count($matches) !== 3) {
                $form->get('product_url')->addError((new FormError('L\'url produit n\'est pas valide.')));

                return $this->render('admin/forms/defaultForm.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $productAlertCheck = $productAlertRepository->findOneBy([
                'productName' => $matches[1],
                'productId' => $matches[2],
                'store' => $productAlert->getStore(),
                'user' => $this->getUser(),
            ]);

            if ($productAlertCheck) {
                $form->get('product_url')->addError((new FormError('Vous avez déjà ajouté ce produit')));

                return $this->render('admin/forms/defaultForm.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $productAlert
                ->setProductId($matches[2])
                ->setProductName($matches[1])
                ->setUser($this->getUser());


            $em->persist($productAlert);
            $em->flush();

            return new Response(null);
        }

        return $this->render('admin/forms/defaultForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/edit/{productAlert}", name="edit_product")
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ProductAlert $productAlert
     *
     * @return Response
     */
    public function editProductForm(EntityManagerInterface $em, Request $request, ProductAlert $productAlert)
    {
        $form = $this->createForm(ProductAlertEditType::class, $productAlert, [
            'action' => $this->generateUrl('dashboard_edit_product', ['productAlert' => $productAlert->getId()])
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($productAlert);
            $em->flush();

            return new Response(null);
        }

        return $this->render('admin/forms/defaultForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/product/{productAlert}", name="delete_product")
     *
     * @param EntityManagerInterface $em
     * @param ProductAlert $productAlert
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteProductAlert(EntityManagerInterface $em, ProductAlert $productAlert)
    {
        if ($this->getUser()->getId() !== $productAlert->getUser()->getId()) {
            return $this->redirectToRoute('dashboard_missing_product');
        }

        $em->remove($productAlert);
        $em->flush();

        return $this->redirectToRoute('dashboard_missing_product');
    }
}
