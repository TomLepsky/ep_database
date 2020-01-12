<?php include(ROOT . '/views/header.php'); ?>

<main>
	<article class="node-info" id="node-info">
		<?php if (isset($this->message))
			require_once(ROOT . '/views/modules/info.php'); ?>
	</article>
</main>
<aside>
	<p class="text-centered"><a href="/addnewnode/0/<?php echo Node::PARTITION; ?>/" class="more assynch-project">Создать проект</a></p>
	<div class="node-tree" id="tree">
		
	</div>
</aside>

<?php include(ROOT . '/views/footer.php'); ?>

<script>
	$(".assynch-project").on('click', function(event) {
	event.preventDefault(); 
	$('#node-info').load($(this).attr('href'));
});
</script>
