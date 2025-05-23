<?php

declare(strict_types=1);

namespace Kr0lik\DtoToSwagger\PropertyTypeDescriber\Describers;

use BackedEnum;
use Kr0lik\DtoToSwagger\PropertyTypeDescriber\PropertyTypeDescriber;
use Kr0lik\DtoToSwagger\PropertyTypeDescriber\PropertyTypeDescriberInterface;
use Kr0lik\DtoToSwagger\ReflectionPreparer\RefTypePreparer\RefTypePreparer;
use Kr0lik\DtoToSwagger\Register\OpenApiRegister;
use OpenApi\Annotations\Schema;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use ReflectionException;
use Symfony\Component\PropertyInfo\Type;

class EnumDescriber implements PropertyTypeDescriberInterface
{
    public function __construct(
        private OpenApiRegister $openApiRegister,
        private PropertyTypeDescriber $propertyDescriber,
        private RefTypePreparer $refTypePreparer,
    ) {}

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     */
    public function describe(Schema $property, array $context = [], Type ...$types): void
    {
        $class = $types[0]->getClassName();

        if (null === $class) {
            return;
        }

        $path = $this->openApiRegister->findSchemaPath($class);

        if (null !== $path) {
            $property->ref = $path;

            return;
        }

        $reflectionEnum = new ReflectionEnum($class);

        $schema = $this->getSchema($reflectionEnum, $context);

        $property->ref = $this->openApiRegister->registerSchema($schema, $class);
    }

    public function supports(Type ...$types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType()
            && null !== $types[0]->getClassName()
            && is_a($types[0]->getClassName(), BackedEnum::class, true);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getSchema(ReflectionEnum $reflectionEnum, array $context): Schema
    {
        $schema = new Schema([]);

        if (null !== $reflectionEnum->getBackingType()) {
            $this->propertyDescriber->describe($schema, $context, ...$this->refTypePreparer->prepare($reflectionEnum->getBackingType()));
        }

        $schema->enum = array_map(static fn (ReflectionEnumUnitCase $case): mixed => $case->getValue(), $reflectionEnum->getCases());

        return $schema;
    }
}
