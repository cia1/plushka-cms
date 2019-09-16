<?php
namespace plushka\model;
use plushka\core\plushka;

abstract class Payment {

    public const STATUS_STATUS='status';
    public const STATUS_SUCCESS='success';
    public const STATUS_FAIL='fail';

    /**
     * Воздващает массив, описывающй HTML-форму для отправки на платёжный сервер или объект, воспринимаемый как класс формы
     * Если возвращается массив, то каждый элемент может иметь следующие ключи:
     * 'action' - атрибут "action" HTML-тега <form>,
     * 'method' - атрибут "method" HTML-тега <form>,
     * 'field' - массив полей формы, каждый из которых может иметь следующие ключи:
     *   'type', 'name', 'value' - соответствующие атрибуты HTML-тега <input>
     *   'title' - текстовая подпись к полю формы
     * Если параметр $amount не задан, то в форме должно быть поле для ввода произвольной суммы.
     * @param int $paymentId ID платежа (генерируется ДО начала оплаты)
     * @param float|null $amount Сумма, если пользователь должен оплатить фиксированную сумму
     * @return array|Form
     */
    abstract public function formData(int $paymentId,float $amount=null);

    /**
     * Должен вернуть номер платежа, извлекая его из $_POST-данных
     * Этот метод вызывается при обращении платёжного сервера к сайту
     * @param string $action self::STATUS_STATUS, self::STATUS_SUCCESS или self::STATUS_FAIL
     * @return int|string Номер платежа
     */
    abstract public function getPaymentId(string $action): string;

    /**
     * Должен вернуть true, если данные в $_POST или $_GET являются валидным ответом платёжного сервера.
     * @param string $action $action self::STATUS_STATUS, self::STATUS_SUCCESS или self::STATUS_FAIL
     * @param array $paymentInfo Информация о платеже из таблицы payment
     * @return bool Валидны ли данные
     */
    abstract public function validate(string $action,array $paymentInfo): bool;

    /**
     * Должен вернуть сумму платежа, вызывается всегда после self::validate()
     * @param string $action $action self::STATUS_STATUS, self::STATUS_SUCCESS или self::STATUS_FAIL
     * @param array $paymentInfo Информация о платеже из таблицы payment
     * @return float Сумма, которую оплатил пользователь
     */
    abstract public function getAmount(string $action,array $paymentInfo): float;

    private static $_additionalData;

    //Возвращает список доступных методов платежей.
    //Если $form=true, то будут возвращены формы для каждого вида платежа, в противном случае только список методов платежей
    public static function getList($form=true,$additionalData=null) {
        $data=array();
        $cfg=plushka::config('payment');
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
        $name=plushka::translit($name);
        $f=plushka::path().'model/Payment'.ucfirst($name).'.php';
        if(!file_exists($f)) {
            plushka::language('payment');
            plushka::error(LNGPaymentMethodIsNotValid);
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
        $db=plushka::db();
        $data=$db->fetchArrayOnceAssoc('SELECT * FROM payment WHERE id='.$id);
        if($data['data'] && $data['data'][0]=='{') $data['data']=json_decode($data['data'],true);
        return $data;
    }

    //Обновляет сохранённую ранее в базе данных информацию о платеже
    public static function updatePaymentInfo($paymentInfo) {
        $db=plushka::db();
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
        $cfg=plushka::config('payment');
        $this->sandbox=$cfg['sandbox'];
    }

    //Генерирует HTML-форму кнопки "оплатить"
    public function formRender($amount=null,$commission=true) {
        plushka::language('payment');
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
                    if($item['type']!=='hidden') continue;
                    echo '<input type="',$item['type'],'" name="',$item['name'],'" value="',$item['value'],'" />';
                } ?>
                <dl class="form">
                    <?php foreach($data['field'] as $item) {
                        if($item['type']==='hidden') continue;
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
        $cfg=plushka::config('payment');
        if(!isset($cfg[$system])) return null;
        return $cfg[$system];
    }

    //Генерирует и возвращает номер платежа (первичный ключ таблицы payment), сохраняет сопутствующую платежу информацию
    private static function _initPaymentId($additionalData=null) {
        $db=plushka::db();
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
            if(plushka::userId()) $query['userId']=plushka::userId();
            if($additionalData) $query['data']=json_encode($additionalData);
            $db->insert('payment',$query);
            $_SESSION['paymentId']=$db->insertId();
            return $_SESSION['paymentId'];
        } elseif($additionalData) {
            $db=plushka::db();
            $db->query('UPDATE payment SET data='.$db->escape(json_encode($additionalData)).' WHERE id='.$_SESSION['paymentId']);
        }
        return $_SESSION['paymentId'];
    }

}
