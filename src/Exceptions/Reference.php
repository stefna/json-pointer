<?php declare(strict_types=1);

namespace JsonPointer\Exceptions;

final class Reference extends \RuntimeException
{
	public static function cantUseAccessor(): self
	{
		return new self('Provided Accessor does not handle owner');
	}

	public static function notFound(): self
	{
		return new self('Referenced value does not exist');
	}

	public static function elementNotFound(string $element): self
	{
		return new self(sprintf('Referenced element does not exist: %s', $element));
	}
}
