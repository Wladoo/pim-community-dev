<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeLabelsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductMappingAttributesAction
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private GetAttributeLabelsQueryInterface $attributeLabelsQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $catalog = $this->getCatalogQuery->execute($catalogId);
        } catch (CatalogNotFoundException $notFoundException) {
            throw new NotFoundHttpException(previous: $notFoundException);
        }

        $attributeCodes = [];
        foreach ($catalog->getProductMapping() as $source) {
            if ('uuid' === $source['source'] || null === $source['source']) {
                continue;
            }
            $attributeCodes[] = $source['source'];
        }
        $attributes = $this->attributeLabelsQuery->execute($attributeCodes);

        return new JsonResponse($attributes);
    }
}
