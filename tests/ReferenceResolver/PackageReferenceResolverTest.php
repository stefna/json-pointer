<?php declare(strict_types=1);

namespace JsonPointer\Tests\ReferenceResolver;

use JsonPointer\Reference;
use JsonPointer\ReferenceResolver\PackageVendorReferenceResolver;
use JsonPointer\ReferenceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PackageReferenceResolverTest extends TestCase
{

	#[DataProvider('references')]
	public function testReferenceResolving(string $reference, string $expectedId): void
	{
		$x = new PackageVendorReferenceResolver();
		$x->addVendorFolder('node', __DIR__ . '/resources/');
		$ref = Reference::fromString($reference);
		try {
			$doc = $x->resolve($ref);
			$this->assertSame($expectedId, $doc->getId());
		}
		catch (\Throwable $e) {
			$this->fail($e->getMessage());
		}
	}

	public static function references(): array
	{
		return [
			'package with custom map index' => ['@stefna/package-1:#/models/Status', 'Status.json'],
			'package with path' => ['@stefna/package-1:schema/models/Status.json', 'Status.json'],
			'package in default map index' => ['@stefna/package-1:#/Status', 'Status.json'],
			'package with striped @ custom map index' => ['@stefna/package-2:#/model/Test', 'Test.yaml'],
			'package with path missing extension' => ['@stefna/package-2:model/Test2', 'Test2.yml'],
			'package resolving to yaml file' => ['@stefna/package-2:#/payload/Test3', 'Test3.yml'],
		];
	}
}
