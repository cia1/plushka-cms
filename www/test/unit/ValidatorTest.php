<?php
class ValidatorTest extends \PHPUnit\Framework\TestCase {

	private const RULE=[
		'bool'=>['boolean','BOOL'],
		'captcha'=>['captcha','CAPTCHA',false],
		'callback'=>['callback','CALLBACK',false,'ValidatorTest::_callback'],
		'date'=>['date','DATE'],
		'email'=>['email','E-MAIL'],
		'float'=>['float','FLOAT','min'=>50.5,'max'=>60.5],
		'html'=>['html'],
		'image'=>['image','PICTURE','minWidth'=>30],
		'integer'=>['integer','INTEGER','min'=>18,'max'=>100],
		'latin'=>['latin','LATIN','max'=>10],
		'regular'=>['regular','REGULAR',false,'^\d\d\-[a-z]+$'],
		'string'=>['string','STRING','min'=>10,'max'=>30,'trim'=>true]
	];

	private static $validator;

	public static function setUpBeforeClass(): void {
		ob_start();
		plushka::import('captcha');
		ob_end_clean();
		self::$validator=new \plushka\core\Validator();
	}

	public function testBoolean() {
		$this->_testValid('bool',true,true);
		$this->_testValid('bool','1',true);
		$this->_testValid('bool','0',false);
	}

	public function testCaptcha() {
		$this->_testValid('captcha',(string)$_SESSION['captcha'],(int)$_SESSION['captcha']);
		$this->_testInvalid('captcha','12345');
	}

	public function testCallback() {
		$this->_testValid('callback','true-value','TRUE');
		$this->_testInvalid('callback','any-value');
	}

	public function testDate() {
		$this->_testValid('date','1515704400',1515704400);
		$this->_testValid('date','12.01.2018',1515704400);
		$this->_testValid('date',1000,1000);
		$this->_testValid('date','2019-05-15 16:47:15',1557928035);
		$this->_testInvalid('date','41.09.1984');
		$this->_testInvalid('date',100);
	}

	public function testEmail() {
		$this->_testValid('email','valid@example.com','valid@example.com');
		$this->_testInvalid('email','wrong email@address.com');
	}

	public function testFloat() {
		$this->_testValid('float',60,60.0);
		$this->_testValid('float','60.20',60.2);
		$this->_testInvalid('float','one');
		$this->_testInvalid('float',10);
		$this->_testInvalid('float',150.2);
	}

	public function testHtml() {
		$this->_testValid('html','<b>bold</b>','<b>bold</b>');
	}

	/**
	 * Проверяет загрузку изображения в разных форматах.
	 * @see PictureTest - проверяет обработку изображения
	 */
	public function testImage() {
		$this->_testValid('image','admin/public/icon/setting32.png');
		$this->_testValid('image',[
			'size'=>2216,
			'type'=>'image/png',
			'tmpName'=>plushka::path().'/admin/public/icon/delete32.png'
		]);
		$this->_testValid('image',[
			[
				'size'=>2216,
				'type'=>'image/png',
				'tmpName'=>plushka::path().'/admin/public/icon/delete32.png'
			],[
				'size'=>2216,
				'type'=>'image/png',
				'tmpName'=>plushka::path().'/admin/public/icon/delete32.png'
			]
		]);
		$this->_testInvalid('image','admin/public/icon/setting16.png');
		$this->_testInvalid('image',[
			[
				'size'=>2216,
				'type'=>'image/png',
				'tmpName'=>plushka::path().'/admin/public/icon/delete32.png'
			],[
				'size'=>2216,
				'type'=>'image/png',
				'tmpName'=>plushka::path().'/admin/public/icon/delete16.png'
			]
		]);
	}

	public function testInteger() {
		$this->_testValid('integer',25,25);
		$this->_testValid('integer',56.3,56);
		$this->_testInvalid('integer',10);
		$this->_testInvalid('integer',120);
	}

	public function testLatin() {
		$this->_testValid('latin','hello-all','hello-all');
		$this->_testInvalid('latin','hello-world');
		$this->_testInvalid('latin','Привет');
	}

	public function testRegular() {
		$this->_testValid('regular','02-febrary','02-febrary');
		$this->_testInvalid('regular','2-febrary');
	}

	public function testString() {
		$this->_testValid('string',' Hello, <b>world</b>!','Hello, world!');
		$this->_testInvalid('string','Hello, world! This string is too long.');
		$this->_testInvalid('string','Too short');
	}



	public static function _callback($value) {
		switch($value) {
		case null;
			return null;
		case 'true-value':
			return 'TRUE';
		default:
			plushka::error('Недопустимое значение');
			return null;
		}
	}

	private function _testInvalid(string $attribute,$data) {
		plushka::error(false);
		self::$validator->set([$attribute=>$data]);
		$this->assertFALSE(self::$validator->validate(self::RULE));
	}

	private function _testValid(string $attribute,$data,$result=null) {
		plushka::error(false);
		self::$validator->set([$attribute=>$data]);
		$this->assertTRUE(self::$validator->validate(self::RULE));
		if($result!==null) $this->assertSame(self::$validator->$attribute,$result);
	}

}