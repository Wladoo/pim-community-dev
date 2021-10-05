<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppIdAndUserGroupQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationAction
{
    private CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler;
    private FeatureFlag $featureFlag;
    private GetConnectedAppIdAndUserGroupQueryInterface $getConnectedAppIdAndUserGroupQuery;
    private SecurityFacade $security;
    private ViolationListNormalizer $violationListNormalizer;
    private LoggerInterface $logger;

    public function __construct(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetConnectedAppIdAndUserGroupQueryInterface $getConnectedAppIdAndUserGroupQuery,
        ViolationListNormalizer $violationListNormalizer,
        SecurityFacade $security,
        LoggerInterface $logger
    ) {
        $this->createAppWithAuthorizationHandler = $createAppWithAuthorizationHandler;
        $this->featureFlag = $featureFlag;
        $this->getConnectedAppIdAndUserGroupQuery = $getConnectedAppIdAndUserGroupQuery;
        $this->violationListNormalizer = $violationListNormalizer;
        $this->security = $security;
        $this->logger = $logger;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedAppIdAndUserGroup = $this->getConnectedAppIdAndUserGroupQuery->execute($clientId);
        if (null !== $connectedAppIdAndUserGroup) {
            return new JsonResponse($connectedAppIdAndUserGroup);
        }

        try {
            $this->createAppWithAuthorizationHandler->handle(new CreateAppWithAuthorizationCommand($clientId));
        } catch (InvalidAppAuthorizationRequest $exception) {
            $this->logger->warning(sprintf('App activation failed with validation error "%s"', $exception->getMessage()));

            return new JsonResponse([
                'errors' => $this->violationListNormalizer->normalize($exception->getConstraintViolationList()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $connectedAppIdAndUserGroup = $this->getConnectedAppIdAndUserGroupQuery->execute($clientId);
        if (null === $connectedAppIdAndUserGroup) {
            throw new \LogicException('The CreateApp handler was executed without error but the resulting App cannot be found');
        }

        return new JsonResponse($connectedAppIdAndUserGroup);
    }
}
