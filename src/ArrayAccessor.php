<?php declare(strict_types=1);

namespace JsonPointer;

final class ArrayAccessor
{
	public function getValue(array $target, string $token)
	{
		$pointedValue = new VoidValue($target, $token);

		if ($this->hasValue($target, $token)) {
			$pointedValue = $target[$token];
		}

		return $pointedValue;
	}

	public function hasValue(&$target, $token): bool
	{
		return array_key_exists($token, $target);
	}

	public function covers(&$target): bool
	{
		return is_array($target);
	}
}
