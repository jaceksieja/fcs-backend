<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Producent;
use AppBundle\Entity\ProducentRepository;
use AppBundle\Form\ProductentType;
use AppBundle\Request\Criteria;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Util\Codes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/producent", service="controller.producent")
 */
class ProducentController
{
    use RestTrait;

    /**
     * @var ProducentRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;
    private $formFactory;

    public function __construct(ProducentRepository $repository, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("", name="producent_list")
     * @Method({"GET"})
     * @ParamConverter("queryCriteria", converter="query_criteria_converter")
     * @Secure(roles="ROLE_ADMIN")
     * @param Criteria $criteria
     *
     *
     * @return \FOS\RestBundle\View\View
     */
    public function indexAction(Criteria $criteria)
    {
        $data = [
            'total' => $this->repository->countByCriteria($criteria),
            'result' => $this->repository->findByCriteria($criteria),
        ];

        return $this->renderRestView($data, Codes::HTTP_OK, [], ['producent_index']);
    }

    /**
     * @Route("", name="producent_create")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAction(Request $request)
    {
        return $this->handleForm($request);
    }

    /**
     * @Route("/{id}", name="producent_edit")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\Form\FormInterface
     */
    public function editAction(Request $request, $id)
    {
        /** @var Producent $producent */
        $producent = $this->repository->find($id);

        if (!$producent) {
            throw new NotFoundHttpException(sprintf('Producent %s does not exists', $id));
        }

        return $this->handleForm($request, $producent);
    }

    /**
     * @param Request $request
     * @param null $producent
     * @return \Symfony\Component\Form\FormInterface
     * @internal param $producent
     */
    private function handleForm(Request $request, $producent = null)
    {
        $code = $producent ? Codes::HTTP_OK : Codes::HTTP_CREATED;
        $serializationGroup = $producent ? 'producent_update' : 'producent_create';

        $form = $this->formFactory->createNamed('', new ProductentType(), $producent);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $producent = $form->getData();

            $this->entityManager->persist($producent);
            $this->entityManager->flush($producent);

            return $this->renderRestView($producent, $code, [], [$serializationGroup]);
        }

        return $form;
    }

    /**
     * @Route("/{id}", name="producent_remove")
     * @Method({"DELETE"})
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param $id
     */
    public function removeAction($id)
    {
        /** @var Producent $producent */
        $producent = $this->repository->find($id);

        if (!$producent) {
            throw new NotFoundHttpException(sprintf('Producent %s does not exists', $id));
        }

        $producent->inactivate();

        $this->entityManager->persist($producent);
        $this->entityManager->flush();
    }

    /**
     * @Route("/{id}", name="producent_view")
     * @Method({"GET"})
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param $id
     *
     * @return \FOS\RestBundle\View\View
     */
    public function viewAction($id)
    {
        /** @var Producent $producent */
        $producent = $this->repository->find($id);

        if (!$producent) {
            throw new NotFoundHttpException(sprintf('Producent %s does not exists', $id));
        }

        return $this->renderRestView($producent, Codes::HTTP_OK, [], ['producent_view']);
    }
}
