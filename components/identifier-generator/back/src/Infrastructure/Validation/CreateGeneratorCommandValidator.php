<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Command\CommandInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorCommandValidator implements CommandValidatorInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(CommandInterface $command): void
    {
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            $errorList = new ErrorList();
            foreach ($violations as $violation) {
                $errorList->add(new Error($violation->getMessage(), $violation->getParameters(), $violation->getPropertyPath()));
            }

            throw new ViolationsException($errorList);
        }
    }
}
