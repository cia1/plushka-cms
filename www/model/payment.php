<?php
abstract class payment {

	abstract public function formData($paymentId,$amount=null); //Воздващает массив, описывающй HTML-форму для отправки на платёжный сервер
	abstract public function getPaymentId($action); //Должен вернуть номер платежа, извлекая его из _POST-данных
	abstract public function validate($action,$paymentInfo); //Должен вернуть true, если данные в $_POST или $_GET являются валидным ответом платёжного сервера. $action может быть "status", "success" или "fail"
	abstract public function getAmount($action,$paymentInfo); //Должен вернуть сумму палтежа

	private static $_additionalData;

	//Возвращает список доступных методов платежей.
	//Если $form=true, то будут возвращены формы для каждого вида платежа, в противном случае только список методов платежей
	public static function getList($form=true,$additionalData=null) {
		$data=array();
		$cfg=core::config('payment');
		foreach($cfg as $id=>$item) {
			if($item['rate']<=0) continue;
			if($form) {
				$item=payment::instance($id,$additionalData);
				if(!$item) continue;
				$item->id=$id;
				$data[]=$item;
			} else $data[]=$id;
		}
		return $data;
	}

	//Возвращает экземпляр класса платёжного метода с именем $name
	public static function instance($name,$additionalData=null) {
		$name=core::translit($name);
		$f=core::path().'model/payment-'.$name.'.php';
		if(!file_exists($f)) {
			core::language('payment');
			core::error(LNGPaymentMethodIsNotValid);
			return false;
		}
		include_once($f);
		$name='payment'.ucfirst($name);
		self::$_additionalData=$additionalData;
		$payment=new $name();
		return $payment;
	}

	//Возвращает информацию о платеже (из таблицы payment)
	public static function getInfo($id) {
		$id=(int)$id;
		if(!$id) return false;
		$db=core::db();
		$data=$db->fetchArrayOnceAssoc('SELECT * FROM payment WHERE id='.$id);
		if($data['data'] && $data['data'][0]=='{') $data['data']=json_decode($data['data'],true);
		return $data;
	}

	//Обновляет сохранённую ранее в базе данных информацию о платеже
	public static function updatePaymentInfo($paymentInfo) {
		$db=core::db();
		$query='';
		if(isset($paymentInfo['method']) && $paymentInfo['method']) {
			if($query) $query.=',';
			$query.='method='.$db->escape($paymentInfo['method']);
		}
		if(isset($paymentInfo['status']) && $paymentInfo['status']) {
			if($query) $query.=',';
			$query.='status='.$db->escape($paymentInfo['status']);
		}
		if(isset($paymentInfo['amount']) && $paymentInfo['amount']) {
			if($query) $query.=',';
			$query.='amount='.$db->escape($paymentInfo['amount']);
		}
		if(isset($paymentInfo['data']) && $paymentInfo['data']) {
			if($query) $query.=',';
			if(is_array($paymentInfo['data']) || is_object($paymentInfo['data'])) $paymentInfo['data']=json_encode($paymentInfo['data']);
			$query.='data='.$db->escape($paymentInfo['data']);
		}
		$query='UPDATE payment SET '.$query.' WHERE id='.$paymentInfo['id'];
		return $db->query($query);
	}

	protected $sandbox;

	function __construct() {
		$cfg=core::config('payment');
		$this->sandbox=$cfg['sandbox'];
	}

	//Генерирует HTML-форму кнопки "оплатить"
	public function formRender($amount=null,$commission=true) {
		core::language('payment');
		$rate=$this->config();
		$rate=$rate['rate'];
		$amount=$amount*$rate;
		$data=$this->formData(self::_initPaymentId(self::$_additionalData),$amount);
		if($commission) {
			if($rate==1) $commission=false;
			elseif($rate>1) $commission=$rate*100-100;
			else $commission=false;
		}
		if(is_object($data)) {
			$data->render();
			if($commission) { ?>
				<p class="commission">* Комиссия <?=$commission?>%</p>
			<?php }
		} else { ?>
		<form action="<?=$data['action']?>" method="<?=$data['method']?>">
		<?php foreach($data['field'] as $item) {
			if($item['type']!='hidden') continue;
			echo '<input type="',$item['type'],'" name="',$item['name'],'" value="',$item['value'],'" />';
		} ?>
		<dl class="form">
		<?php foreach($data['field'] as $item) {
			if($item['type']=='hidden') continue;
			if(isset($item['title'])) echo '<dt class="',$item['type'],' ',$item['name'],'">',$item['title'],'</dt>';
			echo '<dd class="',$item['type'],(isset($item['name']) ? ' '.$item['name'] : ''),'"><input type="',$item['type'],'"'.(isset($item['name']) ? ' name="'.$item['name'].'"' : '').' value="',$item['value'],'"',($item['type']=='submit' ? ' class="button submit"' : ''),' /></dd>';
				}
				if($commission) { ?>
			<p class="commission">* Комиссия <?=$commission?>%</p>
		<?php } ?>
		</dl>
		</form>
		<?php }
	}

	protected function config() {
		$system=lcfirst(substr(get_class($this),7));
		$cfg=core::config('payment');
		if(!isset($cfg[$system])) return null;
		return $cfg[$system];
	}

	//Генерирует и возвращает номер платежа (первичный ключ таблицы payment), сохраняет сопутствующую платежу информацию
	private static function _initPaymentId($additionalData=null) {
		$db=core::db();
		if(isset($_SESSION['paymentId'])) { //если статус платежа не "request", то инициализировать новый платёж
			if(!$db->fetchValue('SELECT 1 FROM payment WHERE id='.$_SESSION['paymentId'].' AND status='.$db->escape('request'))) {
				unset($_SESSION['paymentId']);
			}
		}
		if(!isset($_SESSION['paymentId'])) {
			$db->query('DELETE FROM payment WHERE status='.$db->escape('request').' AND date<'.(time()-36400*7));
			$query=array(
				'date'=>time()
			);
			if($userId) $query['userId']=$userId;
			if($additionalData) $query['data']=json_encode($additionalData);
			$db->insert('payment',$query);
			$_SESSION['paymentId']=$db->insertId();
			return $_SESSION['paymentId'];
		} elseif($additionalData) {
			$db=core::db();
			$db->query('UPDATE payment SET data='.$db->escape(json_encode($additionalData)).' WHERE id='.$_SESSION['paymentId']);
		}
		return $_SESSION['paymentId'];
	}

}