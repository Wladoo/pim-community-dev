<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Maps uuid to identifiers and vice versa
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UuidMapping
{
    /** @var array<string, string> */
    private array $uuidsToIdentifiers = [];

    /** @var array<string, string> */
    private array $identifiersToUuids = [];

    /** @var array<string, int> */
    private array $identifiersToIds = [];

    /** @var array<string, int> */
    private array $uuidsToIds = [];

    private function __construct(array $mapping)
    {
        foreach ($mapping as $line) {
            Assert::stringNotEmpty($line['uuid']);
            Assert::string($line['identifier']);
            Assert::numeric($line['id']);
            Assert::notNull($line['id']);
            Assert::true(Uuid::isValid($line['uuid']), sprintf('Invalid uuid "%s"', $line['uuid']));

            $this->uuidsToIdentifiers[$line['uuid']] = $line['identifier'];
            $this->uuidsToIds[$line['uuid']] = $line['id'];

            if (null !== $line['identifier']) {
                $this->identifiersToUuids[$line['identifier']] = Uuid::fromString($line['uuid']);
                $this->identifiersToIds[$line['identifier']] = $line['id'];
            }
        }
    }

    public static function createFromMapping(array $mapping): self
    {
        return new self($mapping);
    }

    public function getUuidFromIdentifier(string $identifier): UuidInterface
    {
        Assert::keyExists($this->identifiersToUuids, $identifier);

        return $this->identifiersToUuids[$identifier];
    }

    public function hasUuid(string $identifier): bool
    {
        return isset($this->identifiersToUuids[$identifier]);
    }

    public function getIdentifier(UuidInterface $uuid): string
    {
        Assert::keyExists($this->uuidsToIdentifiers, $uuid->toString());

        return $this->uuidsToIdentifiers[$uuid->toString()];
    }

    public function hasIdentifier(UuidInterface $uuid): bool
    {
        return isset($this->uuidsToIdentifiers[$uuid->toString()]);
    }

    public function getIdFromIdentifier(string $identifier): int
    {
        return $this->identifiersToIds[$identifier];
    }

    public function getIdFromUuid(string $uuid): int
    {
        return $this->uuidsToIds[$uuid];
    }
}
