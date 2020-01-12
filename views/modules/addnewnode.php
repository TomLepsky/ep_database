<div class="text-centered node">
	<p class="font-height">Добавление нового узла</p>		
</div>
<div class="form-wrapper">
	<form enctype="multipart/form-data" action="/addnewnode/<?php echo $parentId;?>/<?php echo $nodeType; ?>/" method="post">

		<div class="form-row-15">

			<?php if ($nodeType != Node::PARTITION) : ?>
			<div class="form-col-8">
				<label for="prefix">Префикс</label>
				<input type="text" name="prefix" id="prefix" value="КУИЖ" placeholder="" required>
			</div>
			<div class="form-col-33">
				<label for="classifier">Классификатор</label>
				<select name="classifier" id="classifier" onchange="getClassifierNumber();">
					<?php 
						foreach($classifiers as $classifier) {
							echo '<option value="' . $classifier->number . '">' . $classifier->number . ' ' . $classifier->name . '</option>';   
						} 
					?>
				</select>
			</div>
			<div class="form-col-8">
				<label for="number">Номер*</label>
				<input type="text" name="number" id="number"  placeholder="" required>
			</div>
			<?php endif; ?>

			<div class="form-col-33">
				<label for="detail_name">Наименование</label>
				<input type="text" name="name" id="detail_name" placeholder="" required>
			</div>
		</div>

		<?php if ($nodeType == Node::DETAIL) : ?>
		<div class="form-row-15">
			<div class="form-col-74">
				<label for="detail_material">Материал</label>
				<select name="material" id="detail_material">
					<?php 
						foreach($materials as $material) {
							echo '<option value="' . $material->matchingId . '">' . $material->name . ', ' . $material->measure . '</option>';   
						} 
					?>
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_material_quantity">Кол-во материала</label>
				<input type="text" name="material_quantity" id="detail_material_quantity">
			</div>
		</div>
		<?php endif; ?>

		<div class="form-row-15">
			<div class="form-col-23">
				<label for="detail_owner">Разработал</label>
				<select name="owner" id="detail_owner">
					<?php 
					foreach($users as $user) {
						echo '<option value="' . $user->id . '">' . $user->name . '</option>';   
					} 
					?>
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_status">Статус</label>
				<select name="status" id="detail_status">
					<?php 
					foreach($statuses as $status) {
						echo '<option value="' . $status->id . '">' . $status->name . '</option>';   
					}
					?>
				</select>
			</div>
			<?php if ($nodeType != Node::PARTITION) : ?>
			<div class="form-col-23">
				<label for="sheet_format">Формат листа</label>
				<select name="sheet_format" id="sheet_format">
					<?php foreach ($sheetFormats as $format) : ?>
						<option value="<?php echo $format->id; ?>" ><?php echo $format->name; ?></option>
					<?php endforeach; ?>		
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_quantity">Кол-во</label>
				<input type="text" name="quantity" id="detail_quantity" value="1" required>
			</div>
			<?php endif; ?>
		</div>

		<div class="form-row-15">
			<div class="form-col-100">
				<label for="detail_note">Примечание</label>
				<textarea type="text" name="note" id="detail_note" rows="3" value=""></textarea>
			</div>
		</div>

		<?php if ($nodeType == Node::ASSEMBLY && $parentId != 0) : ?>
		<div class="form-row-10">
			<div class="form-col-100">
				<label for="new_folder">Создать папку</label>
				<input type="checkbox"  id="new_folder" name="new_folder" value="yes">	
			</div>
		</div>
		<?php endif; ?>

		<?php if ($nodeType != Node::PARTITION) : ?>
		<div class="form-row-10">
			<div class="form-col-23">
				<div class="file-upload">
					<label>
						<input type="file" name="inputfile2D" id="inputfile2D" onchange="getFile2DName();">
						<span>Чертёж узла</span>
					</label>
				</div>
			</div>
			<div class="form-col-23">
				<div id="file2D-name">

				</div>
			</div>
			<div class="form-col-23">
				<div class="file-upload">
					<label>
						<input type="file" name="inputfile3D" id="inputfile3D" onchange="getFile3DName();">
						<span>Модель узла</span>
					</label>
				</div>
			</div>
			<div class="form-col-23">
				<div id="file3D-name">

				</div>
			</div>
		</div>
		<?php endif; ?>
		<div class="form-row-10">
			<div class="form-col-100">
				<input type="submit" name="submit" id="btn-add" value="Добавить">
				<?php if ($nodeType != Node::PARTITION) echo "<p>* Не рекомендуется менять</p>"; ?>
			</div>
		</div>

	</form>
</div>

<script>

	/*
var files;
 
    $('input[type=file]').on('change', function () {
            files = this.files;
    });

    $("#btn-add").on('click', function (event) {
            event.preventDefault();
            formData = new FormData(this);

            $.each( files, function( key, value ){
                    formData.append( key, value );
            });

            $.ajax({
                    url: $("#add-new-node-form").attr("action"),
                    type: "POST",
                    data: formData,

                    success: function (response) {
                            $('#tree').jstree(true).refresh();
                    }
            });
    });

</script>