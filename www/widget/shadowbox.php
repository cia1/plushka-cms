<?php
/* ������ ���������� Shadowbox, ����� ����� ���� � ����� ����� ��������� ����������� ���������� � ������� */
class widgetShadowbox extends widget {

	public function __invoke() { return true; }

	public function render() {
		echo core::js('jquery.min');
		echo core::js('shadowbox/shadowbox');
		echo '<link rel="stylesheet" type="text/css" href="'.core::url().'public/js/shadowbox/shadowbox.css" />';
		echo '<script>Shadowbox.init();</script>';
	}

}