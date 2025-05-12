<?php

declare(strict_types=1);

namespace Kr0lik\DtoToSwagger\Trait;

use OpenApi\Attributes\Parameter;
use ReflectionProperty;

trait IsRequiredTrait
{
    private function isRequired(ReflectionProperty $reflectionProperty): bool
    {
        foreach($reflectionProperty->getAttributes() as $reflectionAttribute){
            if($reflectionAttribute->getName() === Parameter::class){
                if(isset($reflectionAttribute->getArguments()['required'])){
                    return $reflectionAttribute->getArguments()['required'];
                }
            }
        }

        if ($reflectionProperty->hasDefaultValue()) {
            return false;
        }

        foreach ($reflectionProperty->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $constructorParameter) {
            if ($constructorParameter->getName() === $reflectionProperty->getName() && $constructorParameter->isDefaultValueAvailable()) {
                return false;
            }
        }

        return true;
    }
}
