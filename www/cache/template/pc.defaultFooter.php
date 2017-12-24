
				<div style="clear:both;"></div>
			</div>
			<?=core::section('bottom')?>
			<div style="clear:both;"></div>
		</main>

		<aside>
			<?=core::widget('user')?>
			<?=core::widget('oauth','',null,'Войти через...')?>
			<?=core::section('right')?>
		</aside>

		<div style="clear: both;">&nbsp;</div>
	</div>

	<footer><?=core::section('footer')?></footer>

</body></html>