<?php
declare(strict_types=1);

namespace GibsonOS\Middleware\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Middleware\Model\Instance;

class InstanceRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByToken(string $token): Instance
    {
        return $this->fetchOne('`token`=?', [$token], Instance::class);
    }
}
