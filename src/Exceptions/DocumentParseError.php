<?php declare(strict_types=1);

namespace JsonPointer\Exceptions;

final class DocumentParseError extends \RuntimeException
{
	public static function fileNotFound(string $path): self
	{
		return new self('File not found: ' . $path);
	}

	public static function unknownFormat(string $format): self
	{
		return new self(sprintf(
			'Unknown file format. Supports json and yaml. "%s" provided',
			$format,
		));
	}

	public static function invalidContent(): self
	{
		return new self('File don\'t seam to contain a valid json/yaml document');
	}
}
