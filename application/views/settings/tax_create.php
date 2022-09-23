<article class="content-body">
    <div class="card card-block">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>

            <div class="message"></div>
        </div>
        <div class="card card-block">


            <form method="post" id="data_form" class="card-body">

                <h5><?php echo $this->lang->line('Add') . ' ' . $this->lang->line('Tax') ?></h5>
                <hr>

                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="tname"><?php echo $this->lang->line('Name') ?></label>

                    <div class="col-sm-6">
                        <input type="text" placeholder="Tax Name"
                               class="form-control margin-bottom  required" name="tname">
                    </div>
                </div>
				
				<div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="taxcountryregion">Tax Country Region</label>

                    <div class="col-sm-6">
                        <input type="text" placeholder="Tax Country Region"
                               class="form-control margin-bottom  required" name="taxcountryregion">
                    </div>
                </div>
				
				<div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="taxcode">Tax Code</label>

                    <div class="col-sm-6">
						<select name="taxcode" class="form-control b_input required" id="taxcode">
							<option value="0">Escolha uma Opção</option>
							<?php
								echo $countrys;
							?>

						</select>
                    </div>
                </div>
				<div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="taxdescription">Tax Description</label>

                    <div class="col-sm-6">
                        <input type="text" placeholder="Tax Country Region"
                               class="form-control margin-bottom  required" name="taxdescription">
                    </div>
                </div>

                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="trate"><?php echo $this->lang->line('Rate') ?> (%)</label>

                    <div class="col-sm-6">
                        <input type="number" placeholder="Tax Rate"
                               class="form-control margin-bottom  required" name="trate">
                    </div>
                </div>

                <!--<div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           for="ttype">Type</label>

                    <div class="col-sm-6">
                        <select class="form-control round" name="ttype">
                            <option value="yes" data-tformat="yes">Exclusive</option>
                            <option value="inclusive" data-tformat="incl">Inclusive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"
                           name="ttype2">Type 2</label>

                    <div class="col-sm-6">
                        <select class="form-control round" name="ttype2">
                            <option value="yes" data-tformat="yes">Exclusive</option>
                            <option value="inclusive"
                                    data-tformat="incl">Inclusive</option>
                        </select>
                    </div>
                </div>-->


                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"></label>

                    <div class="col-sm-4">
                        <input type="submit" id="submit-data" class="btn btn-success margin-bottom"
                               value="<?php echo $this->lang->line('Add') ?>" data-loading-text="Adding...">
                        <input type="hidden" value="settings/taxslabs_new" id="action-url">
                    </div>
                </div>


            </form>
        </div>
    </div>
</article>

