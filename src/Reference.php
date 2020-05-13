<?php declare(strict_types=1);

namespace JsonPointer;

final class Reference
{
	private const TYPE_ID = 'id';
	private const TYPE_INTERNAL = 'internal';
	private const TYPE_EXTERNAL = 'external';

	/** @var string */
	private $type;
	/** @var string */
	private $path;
	/** @var string|null */
	private $uri;

	public static function fromString(string $path): self
	{
		if (strpos($path, '#') === 0) {
			if (strpos($path, '/') === 1) {
				return new self(self::TYPE_INTERNAL, substr($path, 1));
			}

			return new self(self::TYPE_ID, substr($path, 1));
		}

		$externalPath = parse_url($path, PHP_URL_FRAGMENT) ?? '';
		return new self(self::TYPE_EXTERNAL, $externalPath, str_replace('#' . $externalPath, '', $path));
	}

	private function __construct(string $type, string $path, string $uri = null)
	{
		$this->type = $type;
		$this->path = $path;
		$this->uri = $uri;
	}

	public function getName(): string
	{
		$parts = array_filter(explode('/', $this->path));

		return end($parts);
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function isInternal(): bool
	{
		return $this->type === self::TYPE_INTERNAL;
	}

	public function isExternal(): bool
	{
		return $this->type === self::TYPE_EXTERNAL;
	}

	public function getUri(): string
	{
		return (string)$this->uri;
	}
}
