<div class="content-body">
    <div class="card yellow-top">
        <div class="card-header">
            <h5 class="title"><?php echo $this->lang->line('Manage Invoices') ?>
                <a href="<?php echo base_url("invoices/create?ty=1")?>" class="btn btn-primary btn-sm btn-new" 
				<?php if ($this->aauth->premission(2) || $this->aauth->get_user()->roleid == 5 || $this->aauth->get_user()->roleid == 7) echo ''; else echo 'hidden' ?>>
                    <?php echo $this->lang->line('Add new') ?></a></h5>
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
                <table id="invoices" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                       width="100%">
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
					<th id="grand_total_1"></th>
					<th id="grand_total_2"></th>
					<th id="grand_total_3"></th>
                    <th class="no-sort">Pago</th>
                    <th><?php echo $this->lang->line('Status') ?></th>
                    <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choise_type_convert" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Converter documento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="convert-id" name="convert-id" value="">
                <input type="hidden" id="convert-type" name="convert-type" value="">
                <input type="hidden" id="convert-ext" name="convert-ext" value="0">
                <select class="form-control b_input required" id="doc-convert-type" name="doc-convert-type">
					<option value="10" data-url="customers_notes/convert"><i class='fa fa-pencil'></i>Nota de Débito</option>
					<option value="11" data-url="customers_notes/convert"><i class='fa fa-pencil'></i>Nota de Crédito</option>
					<option value="13" data-url="receipts/convert"><i class='fa fa-pencil'></i>Recibo</option>
                </select>
            </div>
            <h6 id="titulo_converters" name="titulo_converters"></h6>
            <table id="convertersview" name="convertersview"
                   class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%"></table>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="convert-confirm">Converter Agora
                </button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choise_docs_related" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <input type="hidden" id="relations-id" name="relations-id" value="">
            <input type="hidden" id="relations-type" name="relations-type" value="">
            <input type="hidden" id="relations-type_n" name="relations-type_n" value="">
            <input type="hidden" id="relations-ext" name="relations-ext" value="0">
            <div class="modal-header">
                <h4 class="modal-title">Documentos relacionados</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6 id="titulo_relationt" name="titulo_relationt"></h6>
                <table id="relationstview" name="relationsview"
                       class="table table-striped table-bordered zero-configuration" cellspacing="0"
                       width="100%"></table>
                <h6 id="titulo_relationd" name="titulo_relationd"></h6>
                <table id="relationsdview" name="relationsview"
                       class="table table-striped table-bordered zero-configuration" cellspacing="0"
                       width="100%"></table>
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
                <div class="alert alert-info" id="alert-info-text">
                    <strong>Atenção:</strong> Esta ferramenta permite-lhe colocar um documento em estado anulado, caso
                    cumpra as condições impostas pela Autoridade Tributária.<strong>Ao efetuar esta operação, irá ficar
                        associado e responsabilizado pela operação perante as autoridades competentes.</strong>
                </div>
                <p>Caso já tenha comunicado à Autoridade Tributária o ficheiro SAF-T(PT) referente ao mês do documento
                    que estiver a anular, terá que o voltar a exportar e submeter no eFatura.</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="object-tid" value="">
                <input type="hidden" id="object-tdraft" value="1">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <textarea class="summernote" name="justification_cancel" id="justification_cancel" rows="1"></textarea>
            </div>
            <div class="row" id="centerButton">
                <div class="col-sm-12">
                    <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm">Anular
                    </button>
                    <button type="button" data-dismiss="modal"
                            class="btn"><?php echo $this->lang->line('Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="delete_model2" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">Apagar Documento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <label class='btn-blue' style="display: block;"><span class='fa fa-plus-circle'></span>
                    <strong>Atenção:</strong> Esta ferramenta permite-lhe remover este documento por estar ainda em
                    estado Rascunho.</strong>
                </label>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id2" value="">
                <input type="hidden" id="object-tid2" value="">
                <input type="hidden" id="object-tdraft2" value="0">
                <input type="hidden" id="action-url2" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm2"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choise_type_duplicate" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Em que tipo de documento pretende duplicar?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="duplicate-id" name="duplicate-id" value="">
                <input type="hidden" id="duplicate-type_n" name="duplicate-type_n" value="">
                <input type="hidden" id="duplicate-ext" name="duplicate-ext" value="0">
                <select name="duplicate-type" class="form-control b_input required" id="duplicate-type">
                    <option value="1" data-url="invoices/duplicate"><i class='fa fa-pencil'></i>Fatura</option>
                    <option value="2" data-url="invoices/duplicate"><i class='fa fa-pencil'></i>Fatura Recibo</option>
                    <option value="3" data-url="invoices/duplicate"><i class='fa fa-pencil'></i>Fatura Simplificada
                    </option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="duplicate-confirm">Duplicar
                    Agora
                </button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choise_type" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Que tipo de documento pretende criar?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <a href="<?php echo base_url("invoices/create?ty=1") ?>" class='btn btn-blue'><i
                            class='fa fa-pencil'></i> Fatura</a>
                <a href="<?php echo base_url("invoices/create?ty=2") ?>" class='btn btn-blue'><i
                            class='fa fa-pencil'></i> Fatura Recibo</a>
                <a href="<?php echo base_url("invoices/create?ty=3") ?>" class='btn btn-blue'><i
                            class='fa fa-pencil'></i> Fatura Simplificada</a>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('.summernote').summernote({
            height: 50,
            tooltip: false,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fullscreen', ['fullscreen']],
                ['codeview', ['codeview']]
            ]
        });
    });

</script>
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
		var valsubtotal = 0;
		var valtaxs = 0;
		var valtotals = 0;
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
					typ: 1,
                    end_date: end_date
                }
            },
            'rowCallback': function (row, data, cell) {
                if (data.status == 'canceled') {
                    $(row).css('background-color', ' rgba(255, 0, 39, 0.22)');
                } else if (data.status == 'draft') {
                    $(row).css('background-color', ' rgba(243, 245, 39, 0.2)');
                }
				valsubtotal += parseFloat(data.subtotal);
				valtaxs += parseFloat(data.tax);
				valtotals += parseFloat(data.total);
				//var subtot = amountExchange(valsubtotal, valmulti, valloc);
				//var subtax = amountExchange(valtaxs, valmulti, valloc);
				//var subtots = amountExchange(valtotals, valmulti, valloc);
				$("#grand_total_1").html(valsubtotal.toFixed(2)+'€');
				$("#grand_total_2").html(valtaxs.toFixed(2)+'€');
				$("#grand_total_3").html(valtotals.toFixed(2)+'€');
            },
            'columnDefs': [
                {
                    'targets': [0],
                    'orderable': false,
                },
            ],
			dom: 'Blfrtip',
			pageLength: 10,
			lengthMenu: [10, 20, 50, 100, 200, 500],
			buttons: [
				{
					extend: 'excelHtml5',
					footer: true,
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
					}
				},
				{
					extend: 'csvHtml5',
					title: 'CSV',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
					}
				},
				{
					extend: 'copyHtml5',
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'pdfHtml5',
					title: 'Documentos',
					footer: true,
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
					}
				},
				{ 
					extend: 'colvis', 
					text: '+ Colunas' ,
					exportOptions: {
						columns: ':visible'
					}
				}
			],
        });
    };
</script>