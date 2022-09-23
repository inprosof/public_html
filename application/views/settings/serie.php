<script src="<?php echo assets_url(); ?>assets/portjs/bootstrap-timepicker.min.js" type="text/javascript"></script>
<div class="content">
    <div class="card card-block">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>

            <div class="message"></div>
        </div>
        <div class="card card-block">


            <form method="post" id="data_form" class="card-body">

                <h5><?php echo $this->lang->line('Add') . ' Serie' ?></h5>
                <hr>
				
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active show" id="base-tab1" data-toggle="tab"
						   aria-controls="tab1" href="#tab1" role="tab"
						   aria-selected="true">Geral</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2"
						   href="#tab2" role="tab"
						   aria-selected="false">Números Iniciais</a>
					</li>
				</ul>
				<div class="tab-content px-1 pt-1">
                    <div class="tab-pane active show" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" for="Serie">Serie</label>
							<div class="col-sm-8">
								<input type="text" placeholder="Serie"
									   class="form-control margin-bottom b_input" name="serie">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" for="cae">C.A.E. (Classificação das Atividades Económicas)</label>
							<div class="col-sm-8">
								<select name="cae" class="form-control b_input required" id="cae">
									<option value="">Escolha um CAE</option>
									<?php
										echo $caes;
									?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 control-label" for="from"><?php echo $this->lang->line('Start') ?></label>
							<div class="col-sm-2">
								<input type="text" class="form-control b_input required"
									   placeholder="Start Date" name="startdate"
									   data-toggle="datepicker" autocomplete="false">
							</div>
						</div>
						<div class="form-group row">

							<label class="col-sm-3 control-label"
								   for="from"><?php echo $this->lang->line('End') ?></label>

							<div class="col-sm-2">
								<input type="text" class="form-control b_input required"
									   placeholder="End Date" name="enddate"
									   data-toggle="datepicker" autocomplete="false">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label"
								   for="currency">Inativar?</label>
							<div class="col-sm-2">
								<select name="exclued" class="form-control b_input">
									<option value="0"><?php echo $this->lang->line('No') ?></option>
									<option value="1"><?php echo $this->lang->line('Yes') ?></option>
								</select>
							</div>
						</div>
					</div>
					
					<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
						<?php
						$cvalue = 0;
						foreach ($docs_ini as $row) {
							echo '<div class="col-sm-12">
									<input type="hidden" class="pdIn" name="pid[]" id="pid-' . $cvalue . '" value="' . $row['id'] . '">
									<label class="col-form-label" for="typ_doc_'.$cvalue.'">'.$row['typ_name'].'</label><div class="col-sm-8">
									<input type="text"';
							
							if($row['ver'] == 0)
							{
								echo ' readonly ';
							}	
							echo '"placeholder="'.$row['typ_name'].'" class="form-control margin-bottom b_input" name="start_doc[]" id="doc_'.$cvalue.'" value="'.$row['start'].'">
									</div>
								</div>';
							
							$cvalue++;
						}
						?>
					</div>
				</div>
								
								
								
				

                <div class="form-group row">

                    <label class="col-sm-3 col-form-label"></label>

                    <div class="col-sm-4">
                        <input type="submit" id="submit-data" class="btn btn-success margin-bottom"
                               value="<?php echo $this->lang->line('Add') ?>" data-loading-text="Adding...">
                        <input type="hidden" value="settings/serie" id="action-url">
                    </div>
                </div>


            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.select-box').select2();
</script>