<?php declare(strict_types=1);

namespace JsonPointer\ReferenceResolver;

use JsonPointer\Document;
use JsonPointer\Exceptions\DocumentParseError;
use JsonPointer\Reference;
use JsonPointer\ReferenceResolver\Values\Package;

final class PackageVendorReferenceResolver implements ReferenceResolver
{
	/** @var array<string, string> */
	private array $vendorFolders = [];
	/** @var array<string, string> */
	private array $packageFolders = [];

	public function addPackageFolder(string $name, string $path): void
	{
		$this->packageFolders[$name] = rtrim($path, DIRECTORY_SEPARATOR);
	}

	public function addVendorFolder(string $name, string $path): void
	{
		$this->vendorFolders[$name] = rtrim($path, DIRECTORY_SEPARATOR);
	}

	public function supports(Reference $reference): bool
	{
		if (!$reference->isExternal()) {
			return false;
		}
		return str_starts_with($reference->getPath(), '@') && $reference->getUri();
	}

	public function resolve(Reference $reference): Document
	{
		$referencePackageName = $reference->getPath();
		$packageReference = Reference::fromString($reference->getUri());

		$package = null;
		foreach ([$referencePackageName, substr($referencePackageName, 1)] as $packageName) {
			if (isset($this->packageFolders[$packageName]) && file_exists($this->packageFolders[$packageName])) {
				$package = new Package($this->packageFolders[$packageName]);
				break;
			}

			foreach ($this->vendorFolders as $vendorFolder) {
				$packageFolder = $vendorFolder . DIRECTORY_SEPARATOR . $packageName;
				if (!file_exists($packageFolder)) {
					continue;
				}
				$package = new Package($packageFolder);
				break 2;
			}
		}

		$document = $package?->resolveReference($packageReference);
		if ($document) {
			$reference->setRoot($package?->packageRoot);
			return $document;
		}
		throw DocumentParseError::fileNotFound($reference->getUri());
	}
}
