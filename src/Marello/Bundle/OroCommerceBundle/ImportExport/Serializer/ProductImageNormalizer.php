<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use Oro\Bundle\EntityBundle\ORM\Registry;

class ProductImageNormalizer extends AbstractNormalizer
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @param Registry $registry
     * @param FileManager $fileManager
     */
    public function __construct(Registry $registry, FileManager $fileManager)
    {
        parent::__construct($registry);

        $this->fileManager = $fileManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof File && isset($context['channel'])) {
            if ($product = $this->getProduct($object)) {
                $productData = $product->getData();
                if (isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                    isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']])
                ) {
                    $productImageId = 'product-image-1';
                    if (isset($productData[AbstractProductExportWriter::IMAGE_ID_FIELD]) &&
                        isset($productData[AbstractProductExportWriter::IMAGE_ID_FIELD][$context['channel']])
                    ) {
                        $productImageId =
                            $productData[AbstractProductExportWriter::IMAGE_ID_FIELD][$context['channel']];
                    }
                    $productId = $productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']];
                    $data = [
                        'data' => [
                            'type' => 'productimages',
                            'id' => $productImageId,
                            'relationships' => [
                                'product' => [
                                    'data' => [
                                        'type' => 'products',
                                        'id' => $productId
                                    ]
                                ],
                                'image' => [
                                    'data' => [
                                        'type' => 'files',
                                        'id' => 'file-1'
                                    ]
                                ]
                            ]
                        ],
                        'included' => [
                            [
                                'type' => 'files',
                                'id' => 'file-1',
                                'attributes' => [
                                    'mimeType' => $object->getMimeType(),
                                    'originalFilename' => $object->getOriginalFilename(),
                                    'fileSize' => $object->getFileSize(),
                                    'content' => base64_encode($this->fileManager->getContent($object))
                                ]
                            ],
                        ]
                    ];
                    if ($productImageId === 'product-image-1') {
                        $data['data']['relationships']['types'] = [
                            'data' => [
                                [
                                    'type' => 'productimagetypes',
                                    'id' => 'product-image-type-1'
                                ],
                                [
                                    'type' => 'productimagetypes',
                                    'id' => 'product-image-type-2'
                                ]
                            ]
                        ];
                        array_push(
                            $data['included'],
                            [
                                'type' => 'productimagetypes',
                                'id' => 'product-image-type-1',
                                'attributes' => [
                                    'productImageTypeType' => 'main'
                                ],
                                'relationships' => [
                                    'productImage' => [
                                        'data' => [
                                            'type' => 'productimages',
                                            'id' => $productImageId
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'productimagetypes',
                                'id' => 'product-image-type-2',
                                'attributes' => [
                                    'productImageTypeType' => 'listing'
                                ],
                                'relationships' => [
                                    'productImage' => [
                                        'data' => [
                                            'type' => 'productimages',
                                            'id' => $productImageId
                                        ]
                                    ]
                                ]
                            ]
                        );
                    }

                    return $data;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof File && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
    
    /**
     * @param File $entity
     * @return Product|null
     */
    private function getProduct(File $entity)
    {
        return $this->registry->getRepository(Product::class)->findOneBy(['image' => $entity]);
    }
}
