<?php declare(strict_types=1);

namespace JsonPointer\Exceptions;

final class InvalidPointer extends \RuntimeException
{
	public static function syntax(string $path): self
	{
		return new self('Invalid pointer syntax');
	}
}
