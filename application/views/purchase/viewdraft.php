<div class="content-body">
    <div class="card">
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>
            <div id="thermal_a" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>
            <div id="invoice-template" class="card-body">
			
                <div class="row">
					<div class="title-action">
						<img src="<?php $loc = location($invoice['loc']); echo base_url('userfiles/company/' . $loc['logo']) ?>"
									 class="img-responsive" style="max-height: 80px;">
						<h2 class="btn btn-oval btn-danger">RASCUNHO</h2>
						<div class="btn-group ">
							<?php $validtoken = hash_hmac('ripemd160', $invoice['iid'], $this->config->item('encryption_key'));?>
							<a href="<?php echo 'edit?id=' . $invoice['iid'].'&ty=1'; ?>" class="btn btn-warning mb-1"><i class="fa fa-pencil" ></i> Alterar Rascunho</a>
						 </div>
						 <div class="btn-group ">
							<button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle"
									data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
										class="fa fa-print"></i> <?php echo $this->lang->line('Print') ?>
							</button>
							<div class="dropdown-menu">
								<a class="dropdown-item"
								   href="<?php echo base_url('purchase/printinvoice?id=' . $invoice['iid'] . '&ty=1&token=' . $validtoken); ?>"><?php echo $this->lang->line('Print') ?></a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item"
								   href="<?php echo base_url('purchase/printinvoice?id=' . $invoice['iid'] . '&ty=1&token=' . $validtoken); ?>&d=1"><?php echo $this->lang->line('PDF Download') ?></a>

							</div>
						</div>
					</div>
                </div>
				
				
							
							
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active show" id="base-tab1" data-toggle="tab"
						   aria-controls="tab1" href="#tab1" role="tab"
						   aria-selected="true">Detalhes <?php echo $invoice['irs_type_n']; ?> <strong class="pb-1"> Nº<?php echo $invoice['irs_type_s'].' '.$invoice['serie_name'] . '/' . $invoice['tid']; ?></strong></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2"
						   href="#tab2" role="tab"
						   aria-selected="false">Histórico do Documento</a>
					</li>
				</ul>
				
				<div class="tab-content px-1 pt-1">
                    <div class="tab-pane active show" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
						<!--/ Invoice Company Details -->
						<div class="table-responsive col-sm-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Enc./Orç.</th>
										<th>Refª Cliente</th>
										<th>Moeda</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td><?php echo $invoice['ref_enc_orc']; ?></td>
										<td><?php echo $invoice['refer']; ?></td>
										<td><?php echo $invoice['multiname']; ?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<!-- Invoice Customer Details -->
						<div id="invoice-customer-details" class="row pt-2">
							<div class="col-md-6 col-sm-12 text-xs-center text-md-left">
								<ul class="px-0 list-unstyled">
									<li class="text-bold-800"><li>Código: <?php echo $invoice['csd']?></li><a href="<?php echo base_url('customers/view?id=' . $invoice['cid']) ?>"><strong
											   class="invoice_a"><?php echo $invoice['name'] . '</strong></a></li><li>' . $invoice['company'] . '</li><li>' . $invoice['address'] . '</li><li>' . $invoice['city'] . ',' . $invoice['country'] . '</li><li>' . $this->lang->line('Phone') . ': ' . $invoice['phone'] . '</li><li>' . $this->lang->line('Email') . ': ' . $invoice['email'] . '</li>';
										if (CUSTOM) {
											$c_custom_fields = $this->custom->view_fields_data($invoice['cid'], 1, 1);
											foreach ($c_custom_fields as $row) {
											if ($row['f_type'] == 'text') {
												echo '  <li>' . $row['name'] . ': ' . $row['data'].'</li>';
											}else if ($row['f_type'] == 'check') {
												if($row['data'] == 'on')
													echo '  <li>' . $row['name'] . ': Sim' .'</li>';
												else{
													echo '  <li>' . $row['name'] . ': Não' .'</li>';
												}
											}else if ($row['f_type'] == 'textarea') {
													echo '  <li>' . $row['name'] . ': ' . $row['data'].'</li>';
												}
											}
										}?>
								</ul>

							</div>
							<div class="offset-md-3 col-md-3 col-sm-12 text-xs-center text-md-left">
								<?php echo '<p><span class="text-muted">' . $this->lang->line('Invoice Date') . '  :</span> ' . dateformat($invoice['invoicedate']) . '</p> <p><span class="text-muted">' . $this->lang->line('Due Date') . ' :</span> ' . dateformat($invoice['invoiceduedate']) . '</p>  <p><span class="text-muted">' . $this->lang->line('Terms') . ' :</span> ' . $invoice['termtit'] . '</p><p><span class="text-muted">Série :</span> ' . $invoice['serie_name'] . '</p>';
								?>
							</div>
						</div>
						<!--/ Invoice Customer Details -->

						<!-- Invoice Items Details -->
						<div id="invoice-items-details" class="pt-2">
							<div class="row">
								<div class="table-responsive col-sm-12">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th><?php echo $this->lang->line('Description') ?></th>
												<th class="text-xs-left">Preço</th>
												<th class="text-xs-left">Qtd.</th>
												<th class="text-xs-left"><?php echo $this->lang->line('Discount') ?> %</th>
												<th class="text-xs-left">Imposto</th>
												<th class="text-xs-left">Total Líquido</th>
											</tr>
											</thead>
											<tbody>
											<?php 
												$c = 1;
												$sub_t = 0;

												foreach ($products as $row) {
													$sub_t += $row['subtotal']+$row['totaldiscount'];
													$myArraytaxid = explode(";", $row['taxavals']);
													$valsum = 0;
													foreach ($myArraytaxid as $row1) {
														$valsum += $row1;
													}
													echo '<tr>
														<th scope="row">' . $c . '</th>
															<td><a href="'.base_url('products/edit?id='. $row['pid']).'"><strong class="invoice_a">'.$row['product'].'</strong></a></td>											
															<td>' . amountExchange($row['price'], $invoice['multi'], $invoice['loc']) . '</td>
															 <td>' . amountFormat_general($row['qty']) .' '. $row['unit'] . '</td>
															 <td>' . amountExchange($row['totaldiscount'], $invoice['multi'], $invoice['loc']) . ' (' . amountFormat_s($row['discount']) . $this->lang->line($invoice['format_discount']) . ')</td>
															<td>' . amountExchange($valsum, $invoice['multi'], $invoice['loc']) . ' (' . $row['taxaperc'] . '%)</td>
															<td>' . amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']) . '</td>
														</tr>';
													echo '<tr><td colspan=8>' . $row['product_des'] . '</td></tr>';
													if (CUSTOM) {
														$p_custom_fields = $this->custom->view_fields_data($row['pid'], 5, 1);
														$z_custom_fields = '';
														foreach ($p_custom_fields as $row) {
														if ($row['f_type'] == 'text') {
															$z_custom_fields .= $row['name'] . ': ' . $row['data'] . '<br>';
														}else if ($row['f_type'] == 'check') {
															if($row['data'] == 'on')
																$z_custom_fields .= $row['name'] . ': Sim<br>';
															else{
																$z_custom_fields .= $row['name'] . ': Não<br>';
															}
														}else if ($row['f_type'] == 'textarea') {
																$z_custom_fields .= $row['name'] . ': ' . $row['data'] . '<br>';
															}
														}
														echo '<tr><td colspan="7">' . $z_custom_fields . '&nbsp;</td></tr>';
													}
													$c++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
							<p></p>
							<div class="row">
								<div class="col-md-7 col-sm-12 text-xs-center text-md-left">


									<div class="row">
										<div class="col-md-8"><p class="lead">Estado:
												<u><strong id="pstatus">Rascunho</strong></u>
											</p>
											<p class="lead mt-1"><br><?php echo $this->lang->line('Note') ?>:</p>
											<code>
												<?php echo $invoice['notes'] ?>
											</code>
										</div>
										<div class="text-xs-center">
											<p><?php echo $this->lang->line('Authorized person') ?></p>
											<?php echo '<img src="' . base_url('userfiles/employee_sign/' . $employee['sign']) . '" alt="signature" class="height-100"/>
												<h6>(' . $employee['name'] . ')</h6>
												<p class="text-muted">' . user_role($employee['roleid']) . '</p>'; ?>
										</div>
										
										
									</div>
								</div>
								<div class="col-md-5 col-sm-12">
									<p class="lead"><?php echo $this->lang->line('Summary') ?></p>
									<div class="table-responsive">
										<table class="table">
											<tbody>
											<tr>
												<td><?php echo $this->lang->line('Sub Total') ?></td>
												<td class="text-xs-right"> <?php echo amountExchange($sub_t, 0, $this->aauth->get_user()->loc) ?></td>
											</tr>
											<tr>
												<td><?php echo $this->lang->line('Discount') ?> Comercial</td>
												<td class="text-xs-right"><?php echo amountExchange($invoice['discount'], $invoice['multi'], $invoice['loc']) ?></td>
											</tr>
											
											<?php
												$arrtudo = [];
												foreach ($products as $row) {
													$myArraytaxname = explode(";", $row['taxaname']);
													$myArraytaxcod = explode(";", $row['taxacod']);
													$myArraytaxvals = explode(";", $row['taxavals']);
													for($i = 0; $i < count($myArraytaxname); $i++)
													{
														$jatem = false;
														for($oo = 0; $oo < count($arrtudo); $oo++)
														{
															if($arrtudo[$oo]['title'] == $myArraytaxname[$i])
															{
																$arrtudo[$oo]['val'] = ($arrtudo[$oo]['val']+$myArraytaxvals[$i]);
																$jatem = true;
																break;
															}
														}
														
														if(!$jatem)
														{
															$stack = array('title'=>$myArraytaxname[$i], 'val'=>$myArraytaxvals[$i]);
															array_push($arrtudo, $stack);
														}
													}
												}
												
												for($r = 0; $r < count($arrtudo); $r++)
												{
													echo "<tr>";
													echo "<td>".$arrtudo[$r]['title']."</td>";
													echo '<td class="text-xs-right">'.amountExchange($arrtudo[$r]['val'], 0, $this->aauth->get_user()->loc).'</td>';
													echo "</tr>";
												}
											?>
											<tr>
												<td><?php echo $this->lang->line('Shipping') ?></td>
												<td class="text-xs-right"><?php echo amountExchange($invoice['shipping'], 0, $this->aauth->get_user()->loc) ?></td>
											</tr>
											<tr>
												<td><?php echo $this->lang->line('Discount') ?> Financeiro</td>
												<td class="text-xs-right"><?php echo amountExchange($invoice['discount_rate'], $invoice['multi'], $invoice['loc']) ?></td>
											</tr>
											<tr>
												<td class="text-bold-800"><?php echo $this->lang->line('Total') ?></td>
												<td class="text-bold-800 text-xs-right"> <?php echo amountExchange($invoice['total'], 0, $this->aauth->get_user()->loc) ?></td>
											</tr>
											<tr>
												<td><?php echo $this->lang->line('Payment Made') ?></td>
												<td class="pink text-xs-right">
													(-) <?php echo ' <span id="paymade">' . amountExchange($invoice['pamnt'], 0, $this->aauth->get_user()->loc) ?></span></td>
											</tr>
											<tr class="bg-grey bg-lighten-4">
												<td class="text-bold-800"><?php echo $this->lang->line('Balance Due') ?></td>
												<td class="text-bold-800 text-xs-right"> <?php $myp = '';
													$rming = $invoice['total'] - $invoice['pamnt'];
													if ($rming < 0) {
														$rming = 0;

													}
													echo ' <span id="paydue">' . amountExchange($rming, 0, $this->aauth->get_user()->loc) . '</span></strong>'; ?></td>
											</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
						<div class="row">
							<table class="table table-striped">
								<thead>
								<tr>
									<th>Histórico do documento</th>
									<th>Data</th>

								</tr>
								</thead>
								<tbody id="activity">
								<?php foreach ($history as $row) {

									echo '<tr><td>'.$row['note'].'</td><td>'.$row['created'].'</td></tr>';
								} ?>

								</tbody>
							</table>
						</div>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>


<script src="<?php echo base_url('assets/myjs/jquery.ui.widget.js') ?>"></script>
<script src="<?php echo base_url('assets/myjs/jquery.fileupload.js') ?>"></script>
<script>
    /*jslint unparam: true */
    /*global window, $ */
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = '<?php echo base_url() ?>purchase/file_handling?id=<?php echo $invoice['iid'] ?>';
        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            formData: {'<?php echo$this->security->get_csrf_token_name()?>': crsf_hash},
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('#files').append('<tr><td><a data-url="<?php echo base_url() ?>purchase/file_handling?op=delete&name=' + file.name + '&invoice=<?php echo $invoice['iid'] ?>" class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>');
                });

            },
            progressall: function (e, data) {

                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );

            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });

    $(document).on('click', ".aj_delete", function (e) {
        e.preventDefault();

        var aurl = $(this).attr('data-url');
        var obj = $(this);

        jQuery.ajax({

            url: aurl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                obj.closest('tr').remove();
                obj.remove();
            }
        });

    });
</script>

<!-- Modal HTML -->
<div id="part_payment" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Payment Confirmation') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form class="payment">
                    <div class="row">
                        <div class="col">
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="text" class="form-control" placeholder="Total Amount" name="amount"
                                       id="rmpay"
                                       value="<?php echo $rming ?>">
                                <div class="form-control-position">
                                    <?php echo $this->config->item('currency') ?>
                                </div>

                            </fieldset>


                        </div>
                        <div class="col">
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="text" class="form-control required"
                                       placeholder="Billing Date" name="paydate"
                                       data-toggle="datepicker">
                                <div class="form-control-position">
                      <span class="fa fa-calendar"
                            aria-hidden="true"></span>
                                </div>

                            </fieldset>


                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod"><?php echo $this->lang->line('Payment Method') ?></label>
                            <select name="pmethod" class="form-control mb-1 required">
                                <option value="">Escolha uma Opção</option>
                                <?php echo $metodos_pagamentos;?>
                            </select>
							
							<label for="account"><?php echo $this->lang->line('Account') ?></label>

                            <select name="account" class="form-control">
                                <?php foreach ($acclist as $row) {
                                    echo '<option value="' . $row['id'] . '">' . $row['holder'] . ' / ' . $row['acn'] . '</option>';
                                }
                                ?>
                            </select></div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Note') ?></label>
                            <input type="text" class="form-control"
                                   name="shortnote" placeholder="Short note"
                                   value="Pagamento da Factura: #<?php echo $invoice['irs_type'] . ' '.$invoice['serie'].'/'.$invoice['tid']; ?>"></div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" class="form-control required" name="tid" id="invoiceid" value="<?php echo $invoice['iid'] ?>">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                        <input type="hidden" name="cid" value="<?php echo $invoice['cid'] ?>"><input type="hidden"
                                                                                                     name="cname"
                                                                                                     value="<?php echo $invoice['name'] ?>">
                        <button type="button" class="btn btn-primary"
                                id="submitpayment"><?php echo $this->lang->line('Make Payment'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- cancel -->
<div id="cancel_bill" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Cancel Invoice'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <form class="cancelbill">
                    <?php echo $this->lang->line('You can not revert'); ?>
            </div>
            <div class="modal-footer">
                <input type="hidden" class="form-control"
                       name="tid" value="<?php echo $invoice['iid'] ?>">
				<input type="hidden" class="form-control"
                       name="tid" value="<?php echo $invoice['iid'] ?>">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                <button type="button" class="btn btn-danger"
                        id="send"><?php echo $this->lang->line('Cancel Invoice'); ?></button>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>

<!-- Modal HTML -->
<div id="sendEmail" class="modal fade">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo $this->lang->line('Email'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="request">
                <div id="ballsWaveG">
                    <div id="ballsWaveG_1" class="ballsWaveG"></div>
                    <div id="ballsWaveG_2" class="ballsWaveG"></div>
                    <div id="ballsWaveG_3" class="ballsWaveG"></div>
                    <div id="ballsWaveG_4" class="ballsWaveG"></div>
                    <div id="ballsWaveG_5" class="ballsWaveG"></div>
                    <div id="ballsWaveG_6" class="ballsWaveG"></div>
                    <div id="ballsWaveG_7" class="ballsWaveG"></div>
                    <div id="ballsWaveG_8" class="ballsWaveG"></div>
                </div>
            </div>
            <div class="modal-body" id="emailbody" style="display: none;">
                <form id="sendbill">
                    <div class="row">
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-envelope-o"
                                                                     aria-hidden="true"></span></div>
                                <input type="text" class="form-control" placeholder="Email" name="mailtoc"
                                       value="<?php echo $invoice['email'] ?>">
                            </div>

                        </div>

                    </div>


                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Customer Name'); ?></label>
                            <input type="text" class="form-control"
                                   name="customername" value="<?php echo $invoice['name'] ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Subject'); ?></label>
                            <input type="text" class="form-control"
                                   name="subject" id="subject">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Message'); ?></label>
                            <textarea name="text" class="summernote" id="contents" title="Contents"></textarea></div>
                    </div>

                    <input type="hidden" class="form-control"
                           id="invoiceid" name="tid" value="<?php echo $invoice['iid'] ?>">
                    <input type="hidden" class="form-control"
                           id="emailtype" value=""><input type="hidden" class="form-control"
                           name="attach" value="true">


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                <button type="button" class="btn btn-primary"
                        id="sendM"><?php echo $this->lang->line('Send'); ?></button>
            </div>
        </div>
    </div>
</div>
<!--sms-->
<!-- Modal HTML -->
<div id="sendSMS" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Send'); ?> SMS</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="request_sms">
                <div id="ballsWaveG1">
                    <div id="ballsWaveG_1" class="ballsWaveG"></div>
                    <div id="ballsWaveG_2" class="ballsWaveG"></div>
                    <div id="ballsWaveG_3" class="ballsWaveG"></div>
                    <div id="ballsWaveG_4" class="ballsWaveG"></div>
                    <div id="ballsWaveG_5" class="ballsWaveG"></div>
                    <div id="ballsWaveG_6" class="ballsWaveG"></div>
                    <div id="ballsWaveG_7" class="ballsWaveG"></div>
                    <div id="ballsWaveG_8" class="ballsWaveG"></div>
                </div>
            </div>
            <div class="modal-body" id="smsbody" style="display: none;">
                <form id="sendsms">
                    <div class="row">
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-envelope-o"
                                                                     aria-hidden="true"></span></div>
                                <input type="text" class="form-control" placeholder="SMS" name="mobile"
                                       value="<?php echo $invoice['phone'] ?>">
                            </div>

                        </div>

                    </div>


                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Customer Name'); ?></label>
                            <input type="text" class="form-control"
                                   value="<?php echo $invoice['name'] ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Message'); ?></label>
                            <textarea class="form-control" name="text_message" id="sms_tem" title="Contents"
                                      rows="3"></textarea></div>
                    </div>


                    <input type="hidden" class="form-control"
                           id="smstype" value="">


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                <button type="button" class="btn btn-primary"
                        id="submitSMS"><?php echo $this->lang->line('Send'); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="pop_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Change Status'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form id="form_model">


                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod"><?php echo $this->lang->line('Mark As') ?></label>
                            <select name="status" class="form-control mb-1">
                                <option value="paid"><?php echo $this->lang->line('Paid'); ?></option>
                                <option value="due"><?php echo $this->lang->line('Due'); ?></option>
                                <option value="partial"><?php echo $this->lang->line('Partial'); ?></option>
                            </select>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <input type="hidden" class="form-control required"
                               name="tid" id="invoiceid" value="<?php echo $invoice['iid'] ?>">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                        <input type="hidden" id="action-url" value="invoices/update_status">
                        <button type="button" class="btn btn-primary"
                                id="submit_model"><?php echo $this->lang->line('Change Status'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('.summernote').summernote({
            height: 150,
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

        $('#sendM').on('click', function (e) {
            e.preventDefault();

            sendBill($('.summernote').summernote('code'));

        });


    });


</script>
