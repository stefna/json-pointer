<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\Path;

final class DocumentCollection implements Document
{
	/** @var Document[] */
	private array $documents;

	public function __construct(Document ...$schemas)
	{
		$this->documents = $schemas;
	}

	public function addDocument(Document $document): void
	{
		$this->documents[] = $document;
	}

	public function canResolveReference(Reference $ref): bool
	{
		foreach ($this->documents as $document) {
			if ($document->canResolveReference($ref)) {
				return true;
			}
		}
		return false;
	}

	public function resolveReference(Reference $ref): mixed
	{
		foreach ($this->documents as $document) {
			if (!$document->canResolveReference($ref)) {
				continue;
			}
			$value = $document->resolveReference($ref);
			if ($value !== null) {
				return $value;
			}
		}
		return null;
	}

	public function getId(): string
	{
		return '';
	}

	public function has(string $path): bool
	{
		foreach ($this->documents as $document) {
			if ($document->has($path)) {
				return true;
			}
		}
		return false;
	}

	public function get(string $path = ''): mixed
	{
		foreach ($this->documents as $document) {
			if ($document->has($path)) {
				return $document->get($path);
			}
		}
		return null;
	}

	public function findPathToParent(string $field, mixed $searchValue): string
	{
		$exception = null;
		foreach ($this->documents as $document) {
			try {
				return $document->findPathToParent($field, $searchValue);
			}
			catch (Path $e) {
				$exception = $e;
			}
		}
		if ($exception) {
			throw $exception;
		}
		return '';
	}

	public function findAllPaths(string $query): array
	{
		$result = [];
		foreach ($this->documents as $document) {
			$subResult = $document->findAllPaths($query);
			foreach ($subResult as $path => $value) {
				$result[$path] = $value;
			}
		}
		return $result;
	}
}
