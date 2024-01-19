<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

use App\Repository\ProductRepository;

#[IsGranted("IS_AUTHENTICATED")]
#[Route("/api", "api_", format: "json")]
class ProductController extends AbstractController
{
    // Flags from isSingleElementOrArray function.
    public const ISEOA_SINGLE = 1;
    public const ISEOA_SINGLE_AS_ARRAY = 2;
    public const ISEOA_ARRAY = 3;

    #[Route('/products', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $product_repository): JsonResponse
    {
        $products = $product_repository->findAll();

        return $this->json($products);
    }

    #[Route('/products', name: 'product_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entity_manager): JsonResponse
    {
        $content = $request->getContent(); // Get the json

        $is_single_or_array = $this->isSingleElementOrArray($content); // Find out which type of json is
        $request_body = json_decode($content, true); // Get the json as array only format
        $json_result = [];
        
        switch ($is_single_or_array) {
            case self::ISEOA_SINGLE: // Single element, just store it
                $json_result[] = $this->store($request_body, $entity_manager);
            break;
            case self::ISEOA_SINGLE_AS_ARRAY: // Array, cycle throught the elements if it's not empty
            case self::ISEOA_ARRAY:
                if ($request_body) {
                    foreach ($request_body as $key => $rb) {
                        $json_result[] = $this->store($rb, $entity_manager);
                    }
                }
            break;
        }

        return $this->json($json_result, status: Response::HTTP_CREATED);
    }

    /**
     * Stores in database of product record.
     */
    private function store(array $json_array, EntityManagerInterface $entity_manager): Product
    {
        // Creating
        $product = new Product();
        $product->setSku($json_array['sku']);
        $product->setProductName($json_array['product_name']);
        $product->setDescription($json_array['description']);

        // Saving
        $entity_manager->persist($product);
        $entity_manager->flush();

        return $product;
    }

    #[Route('/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product);
    }

    #[Route("/products/{id}", "product_update", methods: ["PUT"])]
    public function update(Product $product, Request $request, EntityManagerInterface $entity_manager): JsonResponse
    {
        $request_body = json_decode($request->getContent(), true); // Get the json content

        // Making persist() call
        $product = $this->edit($request_body, $product, $entity_manager);

        // Update action
        $entity_manager->flush();

        return $this->json($product);
    }

    #[Route("/products", "product_update_multiple", methods: ["PUT"])]
    public function updateMultiple(Request $request, EntityManagerInterface $entity_manager, ProductRepository $product_repository): JsonResponse
    {
        $content = $request->getContent(); // Get the json

        $is_single_or_array = $this->isSingleElementOrArray($content); // Find out which type of json is
        $request_body = json_decode($content, true); // Get the json as array only format
        $json_result = [];
        
        switch ($is_single_or_array) {
            case self::ISEOA_SINGLE: // Single element, get the product from the sku and update it
                $product = $product_repository->findOneBySku($request_body['sku']);

                if ($product) {
                    $json_result[$request_body['sku']] = [
                        "status" => Response::HTTP_FOUND, 
                        "message" => $this->edit($request_body, $product, $entity_manager)
                    ];
                } else {
                    $json_result[$request_body['sku']] = ["status" => Response::HTTP_NOT_FOUND, "message" => "Not found."];
                }

            break;
            case self::ISEOA_SINGLE_AS_ARRAY: // Array, cycle throught the elements if it's not empty
            case self::ISEOA_ARRAY:
                if ($request_body) {
                    foreach ($request_body as $key => $rb) {
                        $product = $product_repository->findOneBySku($rb['sku']);

                        if ($product) {
                            $json_result[$rb['sku']] = [
                                "status" => Response::HTTP_FOUND, 
                                "message" => $this->edit($rb, $product, $entity_manager)
                            ];
                        } else {
                            $json_result[$rb['sku']] = ["status" => Response::HTTP_NOT_FOUND, "message" => "Not found."];
                        }
                    }
                }
            break;
        }

        // Update action
        $entity_manager->flush();

        return $this->json($json_result);
    }

    /**
     * Edits in database the product record, returns json result or error.
     */
    private function edit(array $json_array, Product $product, EntityManagerInterface $entity_manager): Product
    {
        // Assign new values
        $product->setProductName($json_array['product_name']);
        $product->setDescription($json_array['description']);

        // Persist calls
        $entity_manager->persist($product);

        return $product;
    }

    #[Route('/products/{id}', 'product_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entity_manager): JsonResponse
    {
        $entity_manager->remove($product);
        $entity_manager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Detects whether a json is an array or a single element
     */
    private function isSingleElementOrArray($json) 
    {
        $variable = json_decode($json, false);

        if (is_array($variable)) {
            if (count($variable) == 1) {

                $inner_variable = current($variable);

                if (is_array($inner_variable)) {
                    return self::ISEOA_ARRAY;
                } else {
                    return self::ISEOA_SINGLE_AS_ARRAY;
                }
                
            } else {
                return self::ISEOA_ARRAY;
            }

        } else {
            return self::ISEOA_SINGLE;
        }
    }

}
