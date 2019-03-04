<?php

namespace app\framework;

class Image {

	// jpeg, png, gif, bmp, webp
	public static function open($path) {
		if (!file_exists($path)) {
			return false;
		}
		if (!function_exists("exif_imagetype")) {
			return @imagecreatefromstring(file_get_contents($path));
		}
		$type = @exif_imagetype($path);
		switch ($type) {
		case IMAGETYPE_JPEG:
			return (
				function_exists("imagecreatefrompng")
				? self::fix_orientation(@imagecreatefromjpeg($path), $path)
				: false
			);
		case IMAGETYPE_PNG:
			return (
				function_exists("imagecreatefrompng")
				? self::fix_orientation(@imagecreatefrompng($path), $path)
				: false
			);
		case IMAGETYPE_GIF:
			return function_exists("imagecreatefromgif")
				? self::fix_orientation(@imagecreatefromgif($path), $path)
				: false;
		case IMAGETYPE_BMP:
			return (
				function_exists("imagecreatefrombmp")
				? self::fix_orientation(@imagecreatefrombmp($path), $path)
				: false
			);
		case IMAGETYPE_WEBP:
			return (
				function_exists("imagecreatefromgif")
				? self::fix_orientation(@imagecreatefromwebp($path), $path)
				: false
			);
		default:
			return false;
		}
	}
	
	private static function fix_orientation($image, $path) {
		if (!function_exists("exif_read_data")) {
			return $image;
		}
		$exif = @exif_read_data($path);
		if (!$exif || !is_array($exif)) {
			return $image;
		}
		$exif = array_change_key_case($exif, CASE_LOWER);
		if (!isset($exif['orientation'])) {
			return $image;
		}
		switch ($exif['orientation']) {
		case 1:
			return $image; // normal orientation
		case 2:
			imageflip($image, IMG_FLIP_HORIZONTAL);
			return $image;
		case 3:
			imageflip($image, IMG_FLIP_BOTH);
			return $image;
		case 4:
			imageflip($image, IMG_FLIP_VERTICAL);
			return $image;
		case 5:
			imageflip($image, IMG_FLIP_VERTICAL);
			return imagerotate($image, -90, 0);
		case 6:
			return imagerotate($image, -90, 0);
		case 7:
			imageflip($image, IMG_FLIP_VERTICAL);
			return imagerotate($image, 90, 0);
		case 8:
			return imagerotate($image, 90, 0);
		default:
			return $image;
		}
	}
	
	public static function debug($image) {
		ob_start();
		imagealphablending($image, false);
		imagesavealpha($image, true);
		imagepng($image);
		$image_data = ob_get_contents(); 
		ob_end_clean();
		$base64 = base64_encode($image_data);
		return '<img src="data:image/png;base64,' . $base64 . '"><br>';
	}
	
	public static function rotate_right($image) {
		return imagerotate($image, -90, 0);
	}
	
	public static function rotate_left($image) {
		return imagerotate($image, 90, 0);
	}
	
	public static function flip_horizontal($image) {
		$image = self::copy($image);
		imageflip($image, IMG_FLIP_HORIZONTAL);
		return $image;
	}
	
	public static function flip_vertical($image) {
		$image = self::copy($image);
		imageflip($image, IMG_FLIP_VERTICAL);
		return $image;
	}
	
	public static function copy($image) {
		// TODO: better solution
		return self::rotate_left(self::rotate_right($image));
	}
	
	public static function resize($src_image, $width, $height, $keep_aspect_ratio = false) {
		// TODO: keep_aspect_ratio
		$width = intval($width);
		$height = intval($height);
		if ($width < 1 || $height < 1) {
			return $src_image;
		}
		$src_width = imagesx($src_image);
		$src_height = imagesy($src_image);
		if (!$src_width || !$src_height) {
			return $src_image;
		}
		$target_image = imagecreatetruecolor($width, $height);
		imagealphablending($target_image, false);
		imagesavealpha($target_image, true);
		$result = imagecopyresampled(
			$target_image, $src_image,
			0, 0,
			0, 0,
			$width, $height,
			$src_width, $src_height);
		if (!$result) {
			return $src_image;
		}
		return $target_image;
	}
	
	public static function contain($image, $width, $height) {
		$src_width = imagesx($image);
		$src_height = imagesy($image);
		$scale_factor = 1;
		if ($src_width > $width) {
			$scale_factor = $width / $src_width;
		}
		if ($src_height > $height) {
			$scale_factor = min($scale_factor, $height / $src_height);
		}
		$new_width = floor($src_width * $scale_factor);
		$new_height = floor($src_height * $scale_factor);
		return self::resize($image, $new_width, $new_height, true);
	}
	
	public static function crop($src_image, $x, $y, $width, $height) {
		$x = intval($x);
		$y = intval($y);
		$width = intval($width);
		$height = intval($height);
		if ($width < 1 || $height < 1) {
			return self::copy($src_image);
		}
		$src_width = imagesx($src_image);
		$src_height = imagesy($src_image);
		if (!$src_width || !$src_height) {
			return $src_image;
		}
		if ($x < 0 || $x > $src_width - 1) {
			return $src_image;
		}
		if ($y < 0 || $y > $src_height - 1) {
			return $src_image;
		}
		if ($x + $width > $src_width) {
			$width = $src_width - $x;
		}
		if ($y + $height > $src_height) {
			$height = $src_height - $y;
		}
		$target_image = imagecreatetruecolor($width, $height);
		imagealphablending($target_image, false);
		imagesavealpha($target_image, true);
		$result = imagecopyresampled(
			$target_image, $src_image,
			0, 0,
			$x, $y,
			$width, $height,
			$width, $height);
		if (!$result) {
			return $src_image;
		}
		return $target_image;
	}
	
	public static function cover($src_image, $width, $height, $resize = true) {
		$width = intval($width);
		$height = intval($height);
		if ($width < 1 || $height < 1) {
			return $image;
		}
		$src_width = imagesx($src_image);
		$src_height = imagesy($src_image);
		$target_ration = $width / $height;
		$source_ratio = $src_width / $src_height;
		$new_width = floor(
			$source_ratio > $target_ration
			? $width * $src_height / $height
			: $src_width
		);
		$new_height = floor(
			$source_ratio > $target_ration
			? $src_height
			: $height * $src_width / $width
		);
		$x = floor(($src_width - $new_width) / 2);
		$y = floor(($src_height - $new_height) / 2);
		
		// TODO: check bounds of new_height, new_width, x and y
		
		$cropped = self::crop($src_image, $x, $y, $new_width, $new_height);
		if ($resize) {
			return self::resize($cropped, $width, $height);
		}
		return $cropped;
	}
	
	public static function layer($bg_image, $top_image, $x = 0, $y = 0) {
		$x = intval($x);
		$y = intval($y);
		
		$bg_width = imagesx($bg_image);
		$bg_height = imagesy($bg_image);
		if (!$bg_width || !$bg_height) {
			return $bg_image;
		}
		$width = imagesx($top_image);
		$height = imagesy($top_image);
		if (!$width || !$height) {
			return $bg_image;
		}
		// Ha negatív a koordináta, akkor az ellenkezõ sarokból számol
		if ($x < 0) {
			$x = abs($bg_width + $x) - $width;
		}
		if ($y < 0) {
			$y = abs($bg_height + $y) - $height;
		}
		$target_image = self::copy($bg_image);
		$result = imagecopyresampled(
			$target_image, $top_image,
			$x, $y,
			0, 0,
			$width, $height,
			$width, $height);
		if (!$result) {
			return $src_image;
		}
		return $target_image;
	}
	
	public static function save($image, $path, $quality = 90) {
		imagejpeg($image, $path, $quality);
	}
}

?>