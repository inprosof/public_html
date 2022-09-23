 <?php if(isset($_GET['er'])) echo "License code expired or not set !!!";  ?><br>

<article class="content-body">
    <div class="card card-block">
	
	
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>

            <div class="message"></div>
        </div>
		
        <form method="post" id="activ" class="form-horizontal">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                   value="<?php echo $this->security->get_csrf_hash(); ?>">
            <input type="hidden" id="core"
                   value="Activate/activate2"> 
            <div class="card-body">

                <h5><?php echo $this->lang->line('name_soft') ?></h5>
                <hr>


                <div class="form-group row">

                    <label class="col-sm-2 col-form-label" for="invoiceprefix"><?php echo $this->lang->line('Email') ?></label>

                    <div class="col-sm-6">
                        <input type="text"
                               class="form-control margin-bottom  required" name="email"
                               placeholder="">
                    </div>
                </div>

                <div class="form-group row">

                    <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('Code') ?></label>

                    <div class="col-sm-6">
                        <input type="text" placeholder="code"
                               class="form-control margin-bottom  required" name="code"
                        >
                    </div>
                </div>

                <div class="form-group row">

                    <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('License') ?></label>

                    <div class="col-sm-6">
                        <p>Single Use Standard License. <!--Read The Full License Here 
							<a href="licenca1">Licença 1</a>.
                           <a href="licenca2">Licença 2</a>.
						   <a href="licenca3">Licença 3</a>. -->
                        </p>
                    </div>
                </div>


                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"></label>

                    <div class="col-sm-4">
                        <input type="submit" id="upda" class="btn btn-success margin-bottom"
                               value="Update" data-loading-text="Updating...">
                    </div>
                </div>

            </div>
        </form>
    </div>
</article>

