<div>
    <div style="padding-bottom: 15px;">Страница <?=$parameters['page_number'];?> из <?=$parameters['pages_count'];?></div>
	<div class="icon_big" style="padding-right: 10px;">
		<?if($parameters['previous_page_href'] != ''):?>
			<div class="navigation-button">
				<a href="<?=$parameters['previous_page_href'];?>">
					<span class="oi" data-glyph="arrow-circle-left" title="Предыдущая страница" aria-hidden="true"></span>
				</a>
			</div>
			
		<?endif;?>
		<?if($parameters['next_page_href'] != ''):?>
			<div class="navigation-button">
				<a href="<?=$parameters['next_page_href'];?>">
					<span class="oi" data-glyph="arrow-circle-right" title="Следующая страница" aria-hidden="true"></span>
				</a>
			</div>
		<?endif;?>
	</div>
</div>