<?php
namespace plushka\controller;

use plushka\core\Controller;
use plushka\core\HTTPException;

/**
 * Предназначен для вывода страниц ошибок (преимущественно 404-й HTTP-ошибки).
 * Экземпляр этого контроллера создаётся при отлове HTTPException.
 */
class ErrorController extends Controller {

    protected $code;
    protected $message;

    /**
     * ErrorController constructor.
     * @param HTTPException $e Исключение, которое должен обработать контроллер
     */
    public function __construct(HTTPException $e) {
        parent::__construct();
        $this->url=['error','index'];
        $this->code=$e->getCode();
        $this->message=$e->getMessage();
    }

    public function actionIndex() {
        return 'Default';
    }
}
