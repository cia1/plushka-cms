<?php
function formatPrice($price) {
	$p=(string)$price;
	$l=strlen($p);
	if($l>3) $price=substr($p,0,$l-3).' '.substr($p,$l-3);
	$p=(int)$p[$l-1];
	if($p==1) return $price.' рубль';
	elseif($p<5 && $p!=0) return $price.' рубля';
	else return $price.' рублей';
	return $price;
}

if($this->form['email']) {
	if(!$this->form['subject']) $this->form['subject']='Сообщение с сайта '.$_SERVER['HTTP_HOST'];
	core::import('core/email');
	$e=new email();
	$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
	$e->subject($this->form['subject']);
	$d=array('form'=>'<table>');
	for($i=0;$i<count($this->field);$i++) {
		if($this->field[$i]['htmlType']=='textarea') $d['form'].='<tr><td colspan="2"><b>'.$this->field[$i]['title'].'</b></td></tr><tr><td colspan="2"><i>'.$this->data[$this->field[$i]['id']].'</i></td></tr>';
		else $d['form'].='<tr><td><b>'.$this->field[$i]['title'].'</b></td><td><i>'.$this->data[$this->field[$i]['id']].'</i></td></tr>';
	}
	$d['form'].='</table>';
	$ids=implode(',',array_keys($_SESSION['cart']));
	$db=core::db();
	$db->query('SELECT id,title,price FROM shpProduct WHERE id IN('.$ids.')');
	$d['cart']='<table><tr><th>Наименование</th><th>Количество</th><th>Цена</th><th>Стоимость</th></tr>';
	$totalQuantity=$totalCost=0;
	while($item=$db->fetch()) {
		$q=$_SESSION['cart'][$item[0]]['quantity'];
		$cost=$q*$item[2];
		$totalQuantity+=$q;
		$totalCost+=$cost;
		$d['cart'].='<tr><td>'.$item[1].'</td><td>'.$q.'</td><td>'.formatPrice($item[2]).'</td><td>'.formatPrice($cost).'</td></tr>';
	}
	$d['cart'].='</table>';
	$d['totalQuantity']=$totalQuantity;
	$d['totalCost']=formatPrice($totalCost);
	$e->messageTemplate('admin/shopOrder',$d);
	$e->send($this->form['email']);
}
unset($_SESSION['cart']);
$cfg=core::config('shop');
core::redirect('form/'.$cfg['formId'].'/success');
?>
