<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\Path;

final class DocumentCollection implements DocumentInterface
{
	/** @var DocumentInterface[] */
	private $documents;

	public function __construct(DocumentInterface ...$schemas)
	{
		$this->documents = $schemas;
	}

	public function addDocument(DocumentInterface $document): void
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

	public function resolveReference(Reference $ref)
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

	public function get(string $path = '')
	{
		foreach ($this->documents as $document) {
			if ($document->has($path)) {
				return $document->get($path);
			}
		}
		return null;
	}

	public function findPathToParent(string $field, $searchValue): string
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
}
