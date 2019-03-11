<?php
namespace app\framework;

class UploadedFile {
	public $filename;
	public $basename;
	public $extension;
	
	private $size = null; // file size cache
	private $temp_path;
	
	public function __construct($filename, $temp_path) {
		$this->filename = $filename;
		$this->temp_path = $temp_path;
		$parts = explode('.', $filename);
		if (count($parts) > 1) {
			$this->extension = $parts[count($parts) - 1];
			array_pop($parts);
			$this->basename = implode(".", $parts);
		} else {
			$this->extension = "";
			$this->basename = $filename;
		}
	}
	
	public function size() {
		if ($this->size === null) {
			$this->size = filesize($this->temp_path); // cache
		}
		return $this->size;
	}
	
	public function store() {
		return Storage::add($this->temp_path);
	}
}

?>