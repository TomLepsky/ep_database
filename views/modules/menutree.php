<ul>
	<?php foreach($nodes as $node) : ?>
		<li 
		node_id 	= "<?php echo $node->id; ?>" 
		parent_id 	= "<?php echo $node->parentId; ?>" 
		quantity 	= "<?php echo $node->quantity; ?>"
		<?php if ($node instanceof Assembly) echo 'class="jstree-closed"'; ?>
		>
		<a href="/node/	<?php echo $node->id; ?>/
						<?php echo $node->quantity; ?>/
						<?php echo $node->parentId; ?>/"
		>
		<?php echo $node->fileName; ?>
		</a>
		</li>
	<?php endforeach; ?>
</ul>		