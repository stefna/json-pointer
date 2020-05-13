<?php declare(strict_types=1);

namespace JsonPointer;

interface DocumentInterface
{
	public function getId(): string;

	public function has(string $path): bool;

	public function get(string $path = '');

	public function findPathToParent(string $field, $searchValue): string;

	public function canResolveReference(Reference $ref): bool;

	public function resolveReference(Reference $ref);
}