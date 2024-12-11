<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\InvalidPointer;
use JsonPointer\Exceptions\Path;

final class BasicDocument implements Document, WritableDocument
{
	public static function fromDocument(Document $document): self
	{
		if ($document instanceof self) {
			return $document;
		}

		return new self(
			$document->getId(),
			$document->get(),
		);
	}

	public function __construct(
		private string $id,
		/** @var array<string, mixed> */
		private array $document,
	) {}

	public function getId(): string
	{
		return $this->id;
	}

	public function has(?string $path): bool
	{
		if (!$path) {
			return true;
		}
		try {
			$this->reference($path)->hasValue();
			return true;
		}
		catch (\RuntimeException) {
			return false;
		}
	}

	public function get(?string $path = null): mixed
	{
		return $this->reference($path ?? '')->getValue();
	}

	public function set(string $path, mixed $value): void
	{
		$paths = explode('/', trim($path, '/'));
		$document = &$this->document;
		foreach ($paths as $key) {
			$key = urldecode($key);
			if (!isset($document[$key])) {
				throw Path::notFound($path);
			}
			/** @var array<string, mixed> $document */
			$document = &$document[$key];
		}
		$document = $value;
	}

	public function add(string $path, mixed $value): void
	{
		$paths = explode('/', trim($path, '/'));
		$lastKey = array_key_last($paths);
		$document = &$this->document;
		foreach ($paths as $index => $key) {
			$key = urldecode($key);
			if ($index === $lastKey) {
				$document = &$document[$key];
				break;
			}
			if (!isset($document[$key])) {
				throw Path::notFound($path);
			}
			/** @var array<string, mixed> $document */
			$document = &$document[$key];
		}
		$document = $value;
	}

	public function findPathToParent(string $field, mixed $searchValue): string
	{
		$values = $this->findAll($this->document, $field);
		foreach ($values as $path => $value) {
			if ($searchValue === $value) {
				return str_replace('/' . $field, '', $path);
			}
		}
		throw Path::notFound();
	}

	public function canResolveReference(Reference $ref): bool
	{
		return !($ref->isExternal() && $this->id !== $ref->getUri());
	}

	public function resolveReference(Reference $ref): mixed
	{
		try {
			$path = null;
			if ($ref->isInternal() || ($ref->isExternal() && $this->id === $ref->getUri())) {
				$path = $ref->getPath();
			}
			if ($path === null) {
				try {
					$path = $this->findPathToParent('$id', $ref->getPath());
				}
				catch (Path) {
					return null;
				}
			}

			return $this->get($path);
		}
		catch (\JsonPointer\Exceptions\Reference $e) {
			return null;
		}
	}

	/**
	 * Return list of all $ref elements in document
	 *
	 * Path will point to parent element from $ref this is to make it easy to replace the $ref
	 *
	 * @return array<string, string>
	 */
	public function findAllReferences(): array
	{
		$refs = $this->findAll($this->document, '$ref');
		$result = [];
		foreach ($refs as $key => $ref) {
			$result[substr($key, 0, -5)] = $ref;
		}

		return $result;
	}

	/**
	 * @param array<string, mixed> $document
	 * @return array<string, string>
	 */
	private function findAll(array $document, string $searchField): array
	{
		$return = [];
		foreach ($document as $field => $value) {
			if ($searchField === $field) {
				/** @var string $value */
				$return['/' . $field] = $value;
			}
			elseif (is_array($value)) {
				/** @var array<string, mixed> $value */
				$rs = $this->findAll($value, $searchField);
				foreach ($rs as $resultField => $resultValue) {
					if (str_contains((string)$field, '/')) {
						$field = urlencode($field);
					}
					$return['/' . $field . $resultField] = $resultValue;
				}
			}
		}
		return $return;
	}

	private function reference(string $path): ReferencedValue
	{
		$path = $this->getCleanPath($path);
		if (empty($path)) {
			return new ReferencedValue($this->document);
		}

		return $this->walk($path);
	}

	private function getCleanPath(string $path): string
	{
		$path = $this->getRepresentedPath($path);

		if (!empty($path) && $path[0] !== '/') {
			throw InvalidPointer::syntax($path);
		}

		return $path;
	}

	private function getRepresentedPath(string $path): string
	{
		if (str_starts_with($path, '#')) {
			return urldecode(substr($path, 1));
		}

		return stripslashes($path);
	}

	private function walk(string $path): ReferencedValue
	{
		$target = $this->document;
		$tokens = explode('/', substr($path, 1));

		$accessor = new ArrayAccessor();

		// @phpstan-ignore-next-line - phpstan is just wrong
		while (($token = array_shift($tokens)) !== null) {
			$token = $this->unescape($token);

			if (empty($tokens)) {
				break;
			}

			$value = $accessor->getValue($target, $token);
			if (!is_array($value)) {
				throw InvalidPointer::syntax($path);
			}
			/** @var array<string, mixed> $target */
			$target = $value;
		}

		return new ReferencedValue($target, $token, $accessor);
	}

	private function unescape(string $token): string
	{
		if (preg_match('/~[^01]/', $token)) {
			throw InvalidPointer::syntax($token);
		}

		return str_replace(['~1', '~0'], ['/', '~'], $token);
	}

	public function findAllPaths(string $query): array
	{
		return $this->findAll($this->document, $query);
	}
}
