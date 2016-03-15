<?php

define('MIN_WIDTH', 612);
define('MIN_WIDTH_ACCEPT', 250);
define('MIN_WIDTH_ERROR', "To zdjęcie jest za małe, minimalna szerokość to 250px."); // Min width

define('ABSPATH', dirname(__FILE__) . '/../');
define('NCORE', 'core/');

class Upload {

	private $allowed = array(
		'png', 'jpg', 'jpeg', 'gif',
		'mp4', 'webm', 'flv', 'avi', 'wmv', 'mpeg'
	);
	private $abspath;
	private $status = 'error';
	private $thumbnails = array(640, 612, 328);
	private $name;
	private $extension;

	public function __construct() {
		require ABSPATH . NCORE . 'Kernel.php';
		Kernel::init();

		if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
			$this->moveUserImage();
		}
		echo '{"status":"' . $this->status . '"}';
	}

	public function moveImage() {
		$this->extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
		if (!in_array(strtolower($this->extension), $this->allowed))
			return;

		$this->name = uniqid(date('B_'));
		$newFile = $this->abspath . '/files/tmp/' . $this->name . '.' . $this->extension;

		if ($this->checkIsImage($this->extension) && extension_loaded('imagick')) {
			list($width, $height) = $this->getImageSize($_FILES['upl']['tmp_name']);

			if ($width < MIN_WIDTH_ACCEPT) {
				$this->status = 'error", "msg":"' . MIN_WIDTH_ERROR;
				return 0;
			}

			$image = new Imagick();
			$image->readImage($_FILES['upl']['tmp_name']);

			$bottom = new Imagick();
			$bottom->newImage($width, 28, new ImagickPixel('#1b1b1b'));

			$img = new Imagick();
			$img->readImage($this->abspath . '/web/themes/repostuj/img/watermark.png');
			$bottom->compositeimage($img, Imagick::COMPOSITE_COPY, $width - 160, 0);

			$newImage = new Imagick();
			$newImage->newImage($width, $height + 28, new ImagickPixel('transparent'));

			$newImage->compositeimage($image, Imagick::COMPOSITE_COPY, 0, 0);
			$newImage->compositeImage($bottom, imagick::COMPOSITE_COPY, 0, $height);
			$newImage->setImageFormat($image->getImageFormat());
			file_put_contents($_FILES['upl']['tmp_name'], $newImage); // $newImage->writeImage($newFile);
			$this->createThumbnails($image, $bottom);
		}

		move_uploaded_file($_FILES['upl']['tmp_name'], $newFile);

		$this->status = 'success", "name":"' . $this->name . '.' . $this->extension;
	}

	private function checkIsImage($extension) {
		return (in_array(strtolower($extension), array('png', 'jpg', 'jpeg')));
	}

	private function createThumbnails($image, $bottom) {
		foreach ($this->thumbnails as $size) {
			if ($image->getImageWidth() != $size) {
				$thumbnail = clone $image;
				$thumbnail->scaleImage($size, 0);

				$newThumbnail = new Imagick();
				$newThumbnail->newImage($size, $thumbnail->getImageHeight() + 28, new ImagickPixel('transparent'));

				$newThumbnail->compositeimage($thumbnail, Imagick::COMPOSITE_COPY, 0, 0);
				$newThumbnail->compositeImage($bottom, imagick::COMPOSITE_COPY, $thumbnail->getImageWidth() - $image->getImageWidth(), $thumbnail->getImageHeight());

				$newThumbnail->setImageFormat($image->getImageFormat());
				$newThumbnail->writeImage($this->abspath . '/files/tmp/' . $this->name . '_' . $size . '.' . $this->extension);
			}
		}
	}

	public function moveUserImage() {
		$user = Di::get('Me')->getModel();
		$this->extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
		$this->name = md5($user->getId());

		$width = 128;
		$height = 128;

		$newFile = ABSPATH . 'files/user/' . $this->name . '.' . $this->extension;
		if ($this->checkIsImage($this->extension) && extension_loaded('imagick')) {
			$image = new Imagick();
			$image->readImage($_FILES['upl']['tmp_name']);
			if ($image->getImageWidth() < $image->getImageHeight())
				$image->scaleImage($width, 0);
			else {
				$image->scaleImage(0, $height);
			}

			$x = ceil(($image->getImageWidth() - $width) / 2);
			$y = ceil(($image->getImageHeight() - $height) / 2);

			$image->cropImage($width, $height, $x, $y);
			file_put_contents($_FILES['upl']['tmp_name'], $image);
		}

		$files = glob(ABSPATH . 'files/user/' . $this->name . '*');
		foreach ($files as $file)
			unlink($file);

		move_uploaded_file($_FILES['upl']['tmp_name'], $newFile);
		$this->status = 'success", "name":"' . $this->name . '.' . $this->extension;
	}

	protected function getImageSize($file_path) {
		if (extension_loaded('imagick')) {
			$image = new \Imagick();
			try {
				if (@$image->pingImage($file_path)) {
					$dimensions = array($image->getImageWidth(), $image->getImageHeight());
					$image->destroy();
					return $dimensions;
				}
				return false;
			} catch (Exception $e) {
				error_log($e->getMessage());
			}
		}

		return @getimagesize($file_path);
	}

}

$init = new Upload;
