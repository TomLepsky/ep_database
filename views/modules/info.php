<div class="form-col-100"> 
	<?php 
	if (isset($this->message)) {

		foreach ($this->message as $m) {
			echo '<div class="form-row-1"><p>' . $m . '</p></div>';
		}

	}
	?>
</div>
