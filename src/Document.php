<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\InvalidPointer;
use JsonPointer\Exceptions\Path;

final class Document implements DocumentInterface
{
	private $document;
	/** @var string */
	private $id;

	public function __construct(string $id, array &$document)
	{
		$this->document = $document;
		$this->id = $id;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function has(string $path): bool
	{
		try {
			$this->reference($path)->hasValue();
			return true;
		}
		catch (\RuntimeException $e) {
			return false;
		}
	}

	public function get(string $path = '')
	{
		return $this->reference($path)->getValue();
	}

	public function findPathToParent(string $field, $searchValue): string
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

	public function resolveReference(Reference $ref)
	{
		try {
			$path = null;
			if ($ref->isInternal() || ($ref->isExternal() && $this->id === $ref->getUri())) {
				$path =  $ref->getPath();
			}
			if ($path === null) {
				try {
					$path = $this->findPathToParent('$id', $ref->getPath());
				}
				catch (Path $e) {
					return null;
				}
			}

			return $this->get($path);
		}
		catch (\JsonPointer\Exceptions\Reference $e) {
			return null;
		}
	}

	private function findAll(array $document, string $searchField): array
	{
		$return = [];
		foreach ($document as $field => $value) {
			if (is_array($value)) {
				$rs = $this->findAll($value, $searchField);
				foreach ($rs as $resultField => $resultValue) {
					$return['/' . $field . $resultField] = $resultValue;
				}
			}
			elseif ($searchField === $field) {
				$return['/' . $field] = $value;
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
		if (strpos($path, '#') === 0) {
			return urldecode(substr($path, 1));
		}

		return stripslashes($path);
	}

	private function walk(string $path): ReferencedValue
	{
		$target = $this->document;
		$tokens = explode('/', substr($path, 1));

		$accessor = new ArrayAccessor();

		while (($token = array_shift($tokens)) !== null) {
			$token = $this->unescape($token);

			if (empty($tokens)) {
				break;
			}

			$target = $accessor->getValue($target, $token);
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
}
