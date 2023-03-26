<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Attribute;

use Attribute;
use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Module\Middleware\Service\Attribute\InstanceAttribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class GetInstance implements AttributeInterface
{
    public function getAttributeServiceName(): string
    {
        return InstanceAttribute::class;
    }
}
