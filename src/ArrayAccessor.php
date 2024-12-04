<?php declare(strict_types=1);

namespace JsonPointer;

final class ArrayAccessor
{
	/**
	 * @param array<string, mixed> $target
	 * @return mixed|VoidValue
	 */
	public function getValue(array $target, string $token): mixed
	{
		$pointedValue = new VoidValue($target, $token);

		if ($this->hasValue($target, $token)) {
			/** @var array<string, mixed> $pointedValue */
			$pointedValue = $target[$token];
		}

		return $pointedValue;
	}

	/**
	 * @param array<string, mixed> $target
	 */
	public function hasValue(array $target, string $token): bool
	{
		return array_key_exists($token, $target);
	}

	/**
	 * @phpstan-assert-if-true array<mixed> $target
	 */
	public function covers(mixed $target): bool
	{
		return is_array($target);
	}
}
