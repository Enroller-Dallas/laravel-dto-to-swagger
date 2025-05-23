<?php

declare(strict_types=1);

namespace Kr0lik\DtoToSwagger\PropertyTypeDescriber\Describers;

use DateTimeInterface;
use InvalidArgumentException;
use Kr0lik\DtoToSwagger\Helper\Util;
use Kr0lik\DtoToSwagger\PropertyTypeDescriber\PropertyTypeDescriberInterface;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type;

class DateTimeDescriber implements PropertyTypeDescriberInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @throws InvalidArgumentException
     */
    public function describe(Schema $property, array $context = [], Type ...$types): void
    {
        $property->type = 'string';

        if (null === $property->format || Generator::UNDEFINED === $property->format) {
            $property->format = 'date-time';
        }

        Util::merge($property, $context, true);
    }

    public function supports(Type ...$types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType()
            && null !== $types[0]->getClassName()
            && is_a($types[0]->getClassName(), DateTimeInterface::class, true);
    }
}
