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

	public static function invalidContent(string $file, string $errorMessage = null): self
	{
		$message = sprintf(
			'File "%s" don\'t seam to contain a valid json/yaml document',
			$file,
		);
		if ($errorMessage) {
			$message .= PHP_EOL;
			$message .= sprintf(
				'Error: %s',
				$errorMessage,
			);
		}
		return new self($message);
	}
}
