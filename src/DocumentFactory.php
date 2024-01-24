<?php declare(strict_types=1);

namespace JsonPointer;

final class DocumentFactory
{
	public function __construct(
		private ?string $root = null,
	) {}

	public function createFromFile(string $file): Document&WritableDocument
	{
		if ($this->root && !file_exists($file)) {
			$file = $this->root . ltrim($file, '.');
		}
		if (!file_exists($file)) {
			throw new \InvalidArgumentException('File not found');
		}

		$content = file_get_contents($file);
		$json = json_decode($content, true);
		if (!$json || !is_array($json)) {
			throw new \InvalidArgumentException('File don\'t seam to contain a valid json document');
		}
		return $this->createFromArray(basename($file), $json);
	}

	public function createFromArray(string $id, array $data): DocumentInterface
	{
		if (isset($data['$id'])) {
			$id = rtrim($data['$id'], '/') . '/' . $id;
		}
		elseif (isset($data['id'])) {
			$id = rtrim($data['id'], '/') . '/' . $id;
		}

		return new Document($id, $data);
	}
}
