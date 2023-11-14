<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository;

use Codeception\Test\Unit;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class InstanceRepositoryTest extends Unit
{
    use RepositoryTrait;

    private InstanceRepository $instanceRepository;

    protected function _before()
    {
        $this->loadRepository('middleware_instance');

        $this->instanceRepository = new InstanceRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByToken(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`token`=?', ['galaxy']))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Instance::class),
            $this->instanceRepository->getByToken('galaxy'),
        );
    }
}
