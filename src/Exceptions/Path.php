<?php declare(strict_types=1);

namespace JsonPointer\Exceptions;

final class Path extends \RuntimeException
{
	public static function notFound(): self
	{
		return new self('Json path to value not found');
	}
}
