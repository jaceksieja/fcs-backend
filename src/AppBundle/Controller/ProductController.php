<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductRepository;
use AppBundle\Request\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/product", service="controller.product")
 */
class ProductController
{
    /**
     * @var ProductRepository
     */
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("", name="product_list")
     * @ParamConverter("queryCriteria", converter="query_criteria_converter")
     */
    public function indexAction(Criteria $criteria)
    {
        return [
            'total' => $this->repository->countByCriteria($criteria),
            'result' => $this->repository->findByCriteria($criteria),
        ];
    }
}
