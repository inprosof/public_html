<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo $this->lang->line('Manage Invoices') ?> 
			<a href='#' class="btn btn-primary btn-sm round" data-toggle="modal" data-target="#choise_type" <?php if($this->aauth->premission(2) || $this->aauth->get_user()->roleid == 5 || $this->aauth->get_user()->roleid == 7) echo ''; else echo 'hidden' ?>>
                    <?php echo $this->lang->line('Add new') ?></a></h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2"><?php echo $this->lang->line('Invoice Date') ?></div>
                    <div class="col-md-2">
                        <input type="text" name="start_date" id="start_date"
                               class="date30 form-control form-control-sm" autocomplete="off"/>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm"
                               data-toggle="datepicker" autocomplete="off"/>
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm"/>
                    </div>
                </div>
                <hr>
                <table id="invoices" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Série</th>
                        <th>Nº</th>
						<th>Data Emissão</th>
                        <th>Cliente</th>
                        <th>Contribuinte</th>
						<th>Ilíquido</th>
						<th>Impostos</th>
						<th>Total Liq.</th>
						<th class="no-sort">Pago</th>
                        <th><?php echo $this->lang->line('Status') ?></th>
                        <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
					<tfoot>
						<th>Série</th>
                        <th>Nº</th>
						<th>Data Emissão</th>
                        <th>Cliente</th>
                        <th>Contribuinte</th>
						<th>Ilíquido</th>
						<th>Impostos</th>
						<th>Total Liq.</th>
						<th class="no-sort">Pago</th>
                        <th><?php echo $this->lang->line('Status') ?></th>
                        <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
					</tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choise_type" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Que tipo de Fatura pretende Criar?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
				<a href="<?php echo base_url("invoices/create?ty=1")?>" class='btn btn-blue'><i class='fa fa-pencil'></i> Fatura</a>
                <a href="<?php echo base_url("invoices/create?ty=2")?>" class='btn btn-blue'><i class='fa fa-pencil'></i> Fatura Recibo</a>
				<a href="<?php echo base_url("invoices/create?ty=3")?>" class='btn btn-blue'><i class='fa fa-pencil'></i> Fatura Simplificada</a>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">Anular Documento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Pretende Anular o documento? Esta função é irreversível.</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
				<input type="hidden" id="object-tid" value="">
				<input type="hidden" id="object-tdraft" value="">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm">Anular</button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
		var start_date = $('#start_date').val();
		var end_date = $('#end_date').val();
        draw_data(start_date, end_date);
    });
	
	$('#search').click(function () {
		var start_date = $('#start_date').val();
		var end_date = $('#end_date').val();
		if (start_date != '' && end_date != '') {
			$('#invoices').DataTable().destroy();
			draw_data(start_date, end_date);
		} else {
			alert("Date range is Required");
		}
	});
	
	function draw_data(start_date = '', end_date = '') {
		$('#invoices').DataTable({
			'processing': true,
			'serverSide': true,
			'stateSave': true,
			<?php datatable_lang();?>
			responsive: true,
			'order': [],
			'ajax': {
				'url': "<?php echo site_url('invoices/ajax_list')?>",
				'type': 'POST',
				'data': {
					'<?php echo $this->security->get_csrf_token_name()?>': crsf_hash,
					start_date: start_date,
					end_date: end_date
				}
			},
			'columnDefs': [
				{
					'targets': [0],
					'orderable': false,
				},
			],
			dom: 'Blfrtip',
			buttons: [
				{
					extend: 'excelHtml5',
					footer: true,
					exportOptions: {
						columns: [2, 3, 4, 5, 6, 7]
					}
				}
			],
		});
	};
</script>