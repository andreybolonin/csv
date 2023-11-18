<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv\Serializer;

use ReflectionParameter;
use ReflectionProperty;

use function filter_var;

/**
 * @implements TypeCasting<?float>
 */
final class CastToFloat implements TypeCasting
{
    private readonly bool $isNullable;

    public function __construct(
        ReflectionProperty|ReflectionParameter $reflectionProperty,
        private readonly ?float $default = null,
    ) {
        $this->isNullable = $this->init($reflectionProperty);
    }

    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(?string $value): ?float
    {
        if (null === $value) {
            return match ($this->isNullable) {
                true => $this->default,
                false => throw TypeCastingFailed::dueToNotNullableType('float'),
            };
        }

        $float = filter_var($value, Type::Float->filterFlag());

        return match ($float) {
            false => throw TypeCastingFailed::dueToInvalidValue($value, Type::Float->value),
            default => $float,
        };
    }

    private function init(ReflectionProperty|ReflectionParameter $reflectionProperty): bool
    {
        $type = null;
        $isNullable = false;
        foreach (Type::list($reflectionProperty) as $found) {
            if (!$isNullable && $found[1]->allowsNull()) {
                $isNullable = true;
            }

            if (null === $type && $found[0]->isOneOf(Type::Mixed, Type::Float)) {
                $type = $found;
            }
        }

        if (null === $type) {
            throw throw MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, 'float', 'null', 'mixed');
        }

        return $isNullable;
    }
}
