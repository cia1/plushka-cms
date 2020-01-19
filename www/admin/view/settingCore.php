<?php
use plushka\admin\controller\SettingController;

/**
 * @var SettingController $this
 */
$this->form->render();
?>
<script>
    $('.method select').change(function () {
        if (this.value === 'smtp') {
            $('.smtpHost,.smtpPort,.smtpUser,.smtpPassword').show();
        } else {
            $('.smtpHost,.smtpPort,.smtpUser,.smtpPassword').hide();
        }
    }).change();
</script>
<cite><b>Яндекс.Почта</b>: сервер - ssl://smtp.yandex.ru, порт - 465, логин - e-mail; <b>Gmail</b>: сервер - ssl://smtp.gmail.com, порт - 465, логин - e-mail,
    включить поддержку IMAP, включить "ненадёжные приложения разрешены".</cite>