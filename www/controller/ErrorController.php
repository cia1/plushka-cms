<?php
namespace plushka\controller;

use plushka\core\Controller;
use plushka\core\HTTPException;

class ErrorController extends Controller {

    protected $code;
    protected $message;

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
