<script src="<?php echo assets_url(); ?>assets/portjs/bootstrap-timepicker.min.js" type="text/javascript"></script>
<div class="content">
    <div class="card card-block">
		<div class="card-header">
            <h5><?php echo $this->lang->line('Edit') . ' '.$this->lang->line('Type Docs Tax'); ?></h5>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <form method="post" id="data_form" class="card-body">
			<input type="hidden" name="did" value="<?php echo $irs_typ_doc['id']; ?>">
			<div class="form-group row">

				<label class="col-sm-3 col-form-label" for="Type"><?php echo $this->lang->line('Type') ?></label>

				<div class="col-sm-8">
					<input type="text" placeholder="Type"
						   class="form-control margin-bottom b_input" name="type" value="<?php echo $irs_typ_doc['type'] ?>">
				</div>
			</div>
			<div class="form-group row">

				<label class="col-sm-3 col-form-label" for="Description"><?php echo $this->lang->line('Description') ?></label>

				<div class="col-sm-8">
					<input type="text" placeholder="Description"
						   class="form-control margin-bottom b_input" name="description" value="<?php echo $irs_typ_doc['description'] ?>">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label"
					   for="currency"><?php echo $this->lang->line('Used') ?></label>
				<div class="col-sm-2">
					<select name="used" class="form-control b_input">
						<option value="<?php echo $irs_typ_doc['used'] ?>">--<?php echo $irs_typ_doc['nameused'] ?>--</option>
						<option value="0">Financeiro</option>
						<option value="1">Stocks</option>
						<option value="2">Outros</option>
					</select>
				</div>
			</div>


			<div class="form-group row">

				<label class="col-sm-3 col-form-label"></label>

				<div class="col-sm-4">
					<input type="submit" id="submit-data" class="btn btn-success margin-bottom"
						   value="<?php echo $this->lang->line('Update') ?>" data-loading-text="Adding...">
					<input type="hidden" value="settings/edirs_typ_doc" id="action-url">
				</div>
			</div>


		</form>
    </div>
</div>
<script type="text/javascript">
    $('.select-box').select2();
</script>

<script type="text/javascript"> $('.editdate').datepicker({
		autoHide: true,
		format: '<?php echo $this->config->item('dformat2'); ?>'
	});

	window.onload = function () {
		billUpyog();
	};
</script>