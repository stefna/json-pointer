<?php declare(strict_types=1);

namespace JsonPointer;

final readonly class Reference
{
	public static function fromString(string $path): self
	{
		if (str_starts_with($path, '#')) {
			if (strpos($path, '/') === 1) {
				return new self(ReferenceType::Internal, substr($path, 1));
			}

			return new self(ReferenceType::Id, substr($path, 1));
		}

		if (str_contains($path, ':') && !str_contains($path, '://')) {
			[$part1, $path] = explode(':', $path, 2);
			return new self(
				ReferenceType::External,
				$part1,
				$path,
			);
		}

		$externalPath = parse_url($path, PHP_URL_FRAGMENT) ?? '';
		return new self(
			ReferenceType::External,
			$externalPath ?: $path,
			str_replace('#' . $externalPath, '', $path)
		);
	}

	private function __construct(
		private ReferenceType $type,
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
		return $this->type === ReferenceType::Internal;
	}

	public function isExternal(): bool
	{
		return $this->type === ReferenceType::External;
	}

	public function getUri(): string
	{
		return (string)$this->uri;
	}
}
