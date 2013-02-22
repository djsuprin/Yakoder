<div>
    <div style="padding-bottom: 15px;">Страница <?=$parameters['page_number'];?> из <?=$parameters['pages_count'];?></div>
	<div class="icon_big" style="padding-right: 10px;">
		<?if($parameters['previous_page_href'] != ''):?>
			<div class="navigation-button"><a href="<?=$parameters['previous_page_href'];?>">&#xe013;</a></div>
		<?endif;?>
		<?if($parameters['next_page_href'] != ''):?>
			<div class="navigation-button"><a href="<?=$parameters['next_page_href'];?>">&#xe015;</a></div>
		<?endif;?>
	</div>
</div>