<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Loader;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FixturesJobHelper
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function createJobInstance(array $data): int
    {
        $defaultData = [
            'label' => null,
            'status' => 0,
            'connector' => 'Akeneo CSV Connector',
            'raw_parameters' => [],
            'type' => 'export',
        ];

        $dataToInsert = array_merge($defaultData, $data);
        $dataToInsert['raw_parameters'] = serialize($dataToInsert['raw_parameters']);

        $this->dbalConnection->insert(
            'akeneo_batch_job_instance',
            $dataToInsert
        );

        return (int)$this->dbalConnection->lastInsertId();
    }

    public function createJobExecution(array $data): int
    {
        $defaultData = [
            'status' => 1,
            'raw_parameters' => [],
            'is_stoppable' => true,
            'step_count' => 3,
        ];

        $this->dbalConnection->insert(
            'akeneo_batch_job_execution',
            array_merge($defaultData, $data),
            [
                'raw_parameters' => Types::JSON,
                'is_stoppable' => Types::BOOLEAN,
                'step_count' => Types::INTEGER,
            ]
        );

        return (int)$this->dbalConnection->lastInsertId();
    }

    public function createStepExecution(array $data): int
    {
        $defaultData = [
            'status' => 0,
            'read_count' => 0,
            'write_count' => 0,
            'filter_count' => 0,
            'failure_exceptions' => [],
            'errors' => [],
            'summary' => [],
            'warning_count' => 0,
        ];

        $dataToInsert = array_merge($defaultData, $data);
        $dataToInsert['failure_exceptions'] = serialize($dataToInsert['failure_exceptions']);
        $dataToInsert['errors'] = serialize($dataToInsert['errors']);
        $dataToInsert['summary'] = serialize($dataToInsert['summary']);

        $this->dbalConnection->insert(
            'akeneo_batch_step_execution',
            $dataToInsert
        );

        return (int)$this->dbalConnection->lastInsertId();
    }
}
