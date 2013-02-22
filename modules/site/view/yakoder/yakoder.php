<!DOCTYPE html>

<html>
<head>
    <title><?=Site::$title;?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.png">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,700,300&subset=latin,cyrillic-ext' rel='stylesheet' type='text/css'>
    <link type="text/css" href="/modules/site/view/yakoder/css/yakoder.css" rel="stylesheet" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<?=Site::$head_code;?>
</head>
<body>

<header class="row">
	<div class="four columns header_logo">
		<a href="/">
			<span id="logo_text">Якодер.ру</span><br/>
			<span id="logo_subtext"><nobr>блог программиста</nobr></span>
		</a>
		<div class="logged_block_mobile">
			<?php
				Site::loginBlock();
			?>
		</div>
	</div>
	<nav class="eight columns">
		<a href="/tetrec/game">
			<div class="element">
				Тетрец
				<div class="underline"></div>
			</div>
		</a>
		<a href="/">
			<div class="element">
				Блог
				<div class="underline"></div>
			</div>
		</a>
	</nav>
</header>

<div class="row middle">
	<div class="nine columns content">
		<?=Messager::showMessages();?>
		<?=Site::$content;?>
	</div>

	<aside class="three columns">
		<?=Site::showWidgets(array('Site'=>'widget', 'Blog'=>'widget'));?>
	</aside>
</div>

<footer class="row">
	<div class="twelve columns copyright">&copy; Владимир Васильев, 2013</div>
</footer>

<script type="text/javascript" src="/modules/site/view/yakoder/js/google_analytics.js"></script>

</body>
</html>