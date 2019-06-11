<?php
use plushka\core\Picture;

/**
 * Проверяет корректность обработки файлов
 * @see ValidatorTest::testImage() - проверка загрузки изображения в класс
 */
class PictureTest extends \PHPUnit\Framework\TestCase {

	private const SOURCE_IMAGE_FILE='test/unit/rose.jpg';
	private const TEMP_IMAGE_FILE='tmp/unit-test.jpg';
	private const WATERMARK_IMAGE_FILE='test/unit/glass.png';

	public function testLoadingAndGetSizes(): Picture {
		$picture=new Picture(self::SOURCE_IMAGE_FILE);
		$this->assertNull(plushka::error());
		$dimension=getimagesize(plushka::path().self::SOURCE_IMAGE_FILE);
		$this->assertSame($picture->width(),$dimension[0]);
		$this->assertSame($picture->height(),$dimension[1]);
		return $picture;
	}

	/**
	 * @depends testLoadingAndGetSizes
	 */
	public function testCrop(Picture $picture): Picture {
		$picture->crop(620);
		$this->_saveAndTestSize($picture,620,$picture->height());
		$picture->crop(1,1,11,12);
		$this->_saveAndTestSize($picture,10,11);
		$picture->crop();
		return $picture;
	}

	/**
	 * @depends testCrop
	 */
	public function testResize(Picture $picture): Picture {
		$dimension=getimagesize(plushka::path().self::SOURCE_IMAGE_FILE);
		$picture->resize('<10000','<10000');
		$this->_saveAndTestSize($picture,$dimension[0],$dimension[1]);
		$picture->resize('<500',null);
		$this->_saveAndTestSize($picture,500,333);
		$picture->resize(null,200);
		$this->_saveAndTestSize($picture,300,200);
		return $picture;
	}

	/**
	 * @depends testResize
	 */
	public function testWatermark(Picture $picture) {
		$this->assertTRUE($picture->watermark(self::WATERMARK_IMAGE_FILE,'50%','50%'));
		$this->_saveAndTestSize($picture,640,426);
	}

	private function _saveAndTestSize(Picture $picture, int $width=null,int $height=null) {
		$this->assertSame($picture->save(self::TEMP_IMAGE_FILE),pathinfo(self::TEMP_IMAGE_FILE)['basename']);
		if($width!==null || $height!==null) $dimension=getimagesize(plushka::path().self::TEMP_IMAGE_FILE);
		if($width!==null) $this->assertSame($dimension[0],$width);
		if($height!==null) $this->assertSame($dimension[1],$height);
	}

}