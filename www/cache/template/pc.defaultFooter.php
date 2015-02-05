
				<div style="clear:both;"></div>
			</div>
			<?=core::section('bottom')?>
			<div style="clear:both;"></div>
		</div>

		<div id="sidebar">
			<?=core::widget('user')?>
			<?=core::widget('oauth',null,null,'Войти через...')?>
			<?=core::section('right')?>
		</div>

		<div style="clear: both;">&nbsp;</div>
	</div>

	<div id="footer"><?=core::section('footer')?></div>

</body></html>