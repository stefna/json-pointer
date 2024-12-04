<?php declare(strict_types=1);

namespace JsonPointer\ReferenceResolver\Values;

use JsonPointer\Document;
use JsonPointer\DocumentFactory;
use JsonPointer\Reference;

final class Package
{
	private readonly DocumentFactory $documentFactory;
	public ?string $packageRoot = null;

	public function __construct(
		private readonly string $folder,
	) {
		$this->documentFactory = new DocumentFactory();
	}

	public function resolveReference(Reference $reference): Document|false
	{
		if ($reference->isInternal()) {
			return $this->resolveInternalPackageReference($reference);
		}

		return $this->resolveExternalPackageReference($reference);
	}

	private function resolveInternalPackageReference(Reference $packageReference): false|Document
	{
		$paths = $packageReference->getPath();
		$folderCount = substr_count($paths, '/');
		$mapFile = 'index.json';
		if ($folderCount > 1) {
			[$tmpMap, $tmpRef] = explode(DIRECTORY_SEPARATOR, trim($paths, DIRECTORY_SEPARATOR), 2);
			$mapFile = $tmpMap . '.json';
			$ref = Reference::fromString('#/' . $tmpRef);
		}
		else {
			$ref = $packageReference;
		}

		$mapFile = $this->folder . DIRECTORY_SEPARATOR . $mapFile;
		if (!file_exists($mapFile)) {
			return $this->resolveExternalPackageReference(
				Reference::fromString($packageReference->getPath()),
			);
		}
		$mapDoc = $this->documentFactory->createFromFile($mapFile);
		$resolvedReference = $mapDoc->resolveReference($ref);

		if (
			is_array($resolvedReference)
			&& isset($resolvedReference['$ref'])
			&& is_string($resolvedReference['$ref'])
		) {
			$newRef = Reference::fromString($resolvedReference['$ref']);
			return $this->resolveExternalPackageReference($newRef);
		}

		if (is_array($resolvedReference)) {
			// @phpstan-ignore argument.type
			return $this->documentFactory->createFromArray($ref->getName(), $resolvedReference);
		}

		return $this->resolveExternalPackageReference(Reference::fromString($packageReference->getPath()));
	}

	private function resolveExternalPackageReference(Reference $packageReference): false|Document
	{
		$referenceFile = $this->folder . DIRECTORY_SEPARATOR . ltrim($packageReference->getPath(), './');
		if (file_exists($referenceFile)) {
			$this->packageRoot = dirname($referenceFile);
			return $this->documentFactory->createFromFile($referenceFile);
		}
		$testFiles = [
			$referenceFile . '.json',
			$referenceFile . '.yaml',
			$referenceFile . '.yml',
		];
		foreach ($testFiles as $testFile) {
			if (file_exists($testFile)) {
				$this->packageRoot = dirname($testFile);
				return $this->documentFactory->createFromFile($testFile);
			}
		}

		return false;
	}
}
