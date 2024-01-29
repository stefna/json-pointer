<?php declare(strict_types=1);

namespace JsonPointer;

final readonly class Reference
{
	private const TYPE_ID = 'id';
	private const TYPE_INTERNAL = 'internal';
	private const TYPE_EXTERNAL = 'external';

	public static function fromString(string $path): self
	{
		if (str_starts_with($path, '#')) {
			if (strpos($path, '/') === 1) {
				return new self(self::TYPE_INTERNAL, substr($path, 1));
			}

			return new self(self::TYPE_ID, substr($path, 1));
		}

		$externalPath = parse_url($path, PHP_URL_FRAGMENT) ?? '';
		return new self(
			self::TYPE_EXTERNAL,
			$externalPath ?: $path,
			str_replace('#' . $externalPath, '', $path)
		);
	}

	private function __construct(
		private string $type,
		private string $path,
		private ?string $uri = null,
	) {}

	public function getName(): string
	{
		if ($this->path) {
			$parts = array_filter(explode('/', $this->path));
			return end($parts);
		}
		elseif ($this->uri) {
			return pathinfo($this->uri, PATHINFO_FILENAME);
		}

		throw new \BadMethodCallException('Can\'t find a name for reference');
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
