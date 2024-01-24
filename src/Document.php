<?php declare(strict_types=1);

namespace JsonPointer;

interface Document
{
	public function getId(): string;

	public function has(string $path): bool;

	public function get(string $path = ''): mixed;

	public function findPathToParent(string $field, mixed $searchValue): string;

	public function canResolveReference(Reference $ref): bool;

	public function resolveReference(Reference $ref): mixed;
}
