<div class="content-body">
    <div class="card yellow-top">
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

                    <div class="">
                        <?php
                        $validtoken = hash_hmac('ripemd160', $invoice['iid'], $this->config->item('encryption_key'));?>
						<div class="title-action">
							<img src="<?php $loc = location($invoice['loc']); echo base_url('userfiles/company/' . $loc['logo']) ?>" class="img-responsive" style="max-height: 80px;">
							<div class="btn-group ">
								<button type="button" class="btn btn-primary mb-1 btn-min-width dropdown-toggle"
										data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
											class="fa fa-print"></i> <?php echo $this->lang->line('Print') ?>
								</button>
								<div class="dropdown-menu">
									<a class="dropdown-item"
									   href="<?php echo base_url('invoices/printinvoice?id=' . $invoice['iid'] . '&temp='.INVV.'&draf=0&token=' . $validtoken); ?>"><?php echo $this->lang->line('Print') ?></a>
									<div class="dropdown-divider"></div>
									<a class="dropdown-item"
									   href="<?php echo base_url('invoices/printinvoice?id=' . $invoice['iid'] . '&temp='.INVV.'&draf=0&token=' . $validtoken); ?>&d=1"><?php echo $this->lang->line('PDF Download') ?></a>
								</div>
							</div>
							<?php 
								$validtoken = hash_hmac('ripemd160', $invoice['iid'], $this->config->item('encryption_key'));
								$textbut = 'https://api.whatsapp.com/send?phone='.$invoice['phone'].'&text=';
								$codebase64 = $invoice['iid'] . '&0&'.INVV.'&' . $validtoken;
								$codebase64 = base64_encode($codebase64);
								$endefact = base_url('billing/viewwhat?invoice='.$codebase64);
								$textbut .= 'Caríssimo(a)+Cliente+Nova+Fatura+'.$invoice['irs_type_s'] . '-' . $invoice['serie_name'] . '/' . $invoice['tid'].'+gerada.+'.$endefact.'+Cumprimentos';							
							?>
							<a href="<?php echo $textbut; ?>" target="_blank" class="btn btn-success mb-1"><i class="fa fa-whatsapp"></i>WhatsApp</a>
							<?php if ($invoice['status'] != 'canceled') { ?>
							<!--<php if ($invoice['status'] != 'paid') {
								echo '<a href="#part_payment" data-toggle="modal" data-remote="false" data-type="reminder"
								   class="btn btn-large btn-info mb-1" title="Partial Payment"
								><span class="fa fa-money"></span>'.$this->lang->line('Make Payment').'</a>';
								}?>-->

							<div class="btn-group">
								<button type="button" class="btn btn-facebook dropdown-toggle mb-1"
										data-toggle="dropdown"
										aria-haspopup="true" aria-expanded="false">
							<span
									class="fa fa-envelope-o"></span> Email
								</button>
								<div class="dropdown-menu">
									<a href="#sendEmail" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendbill"
									   data-type="notification"><?php echo $this->lang->line('Invoice Notification') ?></a>
									<div class="dropdown-divider"></div>
									<?php if ($invoice['status'] != 'paid') {
										echo '<a href="#sendEmail" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendbill"
									   data-type="reminder">' . $this->lang->line('Payment Reminder') . '</a>';
									} ?>

									<a href="#sendEmail" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendbill"
									   data-type="received"><?php echo $this->lang->line('Payment Received') ?></a>
									<div class="dropdown-divider"></div>
									<?php if ($invoice['status'] != 'paid') {
										echo '<a href="#sendEmail" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendbill" href="#"
									   data-type="overdue">' . $this->lang->line('Payment Overdue') . '</a>';
									} ?>
									<a href="#sendEmail" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendbill" data-type="refund">
										<?php echo $this->lang->line('Refund Generated') ?></a>

								</div>

							</div>

							<!-- SMS -->
							<div class="btn-group">
								<button type="button" class="btn btn-blue dropdown-toggle mb-1"
										data-toggle="dropdown"
										aria-haspopup="true" aria-expanded="false">
							<span
									class="fa fa-mobile"></span> SMS
								</button>
								<div class="dropdown-menu"><a href="#sendSMS" data-toggle="modal"
															  data-remote="false" class="dropdown-item sendsms"
															  data-type="notification"><?php echo $this->lang->line('Invoice Notification') ?></a>
									<div class="dropdown-divider"></div>
									<?php if ($invoice['status'] != 'paid') {
										echo '<a href="#sendSMS" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendsms"
									   data-type="reminder">' . $this->lang->line('Payment Reminder') . '</a>';
									} ?>
									<a href="#sendSMS" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendsms"
									   data-type="received"><?php echo $this->lang->line('Payment Received') ?></a>
									<div class="dropdown-divider"></div>
									<?php if ($invoice['status'] != 'paid') {
										echo '<a href="#sendSMS" data-toggle="modal" data-remote="false"
										   class="dropdown-item sendsms" href="#"
										   data-type="overdue">' . $this->lang->line('Payment Overdue') . '</a>';

									} ?>
									<a href="#sendSMS" data-toggle="modal" data-remote="false"
									   class="dropdown-item sendsms"
									   data-type="refund"><?php echo $this->lang->line('Refund Generated') ?></a>

								</div>

							</div>
							<a href="<?php echo base_url('billing/view?id=' . $invoice['iid'] . '&temp='.INVV.'&draf=0&token=' . $validtoken); ?>"
							   class="btn btn-blue-grey mb-1"><i
										class="fa fa-globe"></i><?php echo $this->lang->line('Preview') ?></a>
							<div class="btn-group ">
								<button type="button" class="btn btn-primary mb-1 btn-min-width dropdown-toggle"
										data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
											class="icon-anchor"></i> <?php echo $this->lang->line('Extra') ?>
								</button>
								<div class="dropdown-menu">
									<a class="dropdown-item"
									   href="<?php echo base_url() . 'invoices/delivery?id=' . $invoice['iid']; ?>"><?php echo $this->lang->line('Delivery Note') ?></a>
									<div class="dropdown-divider"></div>
									<a class="dropdown-item"
									   href="<?php echo base_url() . 'invoices/printinvoice?id=' . $invoice['iid'].'&temp=1&draf=0&token=' . $validtoken; ?>"><?php echo $this->lang->line('Proforma Invoice') ?></a>
									<a class="dropdown-item"
									   href="<?php echo base_url() . 'invoices/printinvoice?id=' . $invoice['iid'].'&temp=1&d=1&draf=0&token=' . $validtoken; ?>"><?php echo $this->lang->line('Proforma Invoice').' '.$this->lang->line('PDF Download') ?></a>

								</div>
							</div>
							<div class="btn-group ">
								<button type="button" class="btn btn-vimeo mb-1 btn-md dropdown-toggle"
										data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
											class="fa fa-print"></i> <?php echo $this->lang->line('POS Print') ?>
								</button>
								<div class="dropdown-menu">
									<a class="dropdown-item"
									   href="<?php echo base_url() . 'pos_invoices/thermal_pdf?id=' . $invoice['iid']; ?>"><?php echo $this->lang->line('PDF Print') ?></a>
								</div>
							</div>
						<?php
						/*if ($invoice['multi'] > 0) {
							echo '<div class="badge bg-blue text-xs-center mt-2 white">' . $this->lang->line('Payment currency is different') . '</div>';
						}*/
						} else {
							echo '<a href="#" class="btn btn-oval btn-danger">ANULADA</a>';
						} ?>
						
						</div>
					</div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" id="base-tab1" data-toggle="tab"
                           aria-controls="tab1" href="#tab1" role="tab"
                           aria-selected="true">Detalhes <?php echo $invoice['irs_type_n'] ?> <strong class="pb-1">
                                Nº<?php echo $invoice['irs_type_s'] . ' ' . $invoice['serie_name'] . '/' . $invoice['tid']; ?></strong></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2"
                           href="#tab2" role="tab"
                           aria-selected="false">Documentos relacionados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3"
                           href="#tab3" role="tab"
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
                                    <li class="text-bold-800">
                                    <li>Código: <?php echo $invoice['csd'] ?></li>
                                    <a href="<?php echo base_url('customers/view?id=' . $invoice['cid']) ?>"><strong
                                                class="invoice_a"><?php echo $invoice['name'] . '</strong></a></li><li>' . $invoice['company'] . '</li><li>' . $invoice['address'] . '</li><li>' . $invoice['city'] . ',' . $invoice['country'] . '</li><li>' . $this->lang->line('Phone') . ': ' . $invoice['phone'] . '</li><li>' . $this->lang->line('Email') . ': ' . $invoice['email'] . '</li>';
                                            if (CUSTOM) {
                                                $c_custom_fields = $this->custom->view_fields_data($invoice['cid'], 1, 1);
                                                foreach ($c_custom_fields as $row) {
                                                    if ($row['f_type'] == 'text') {
                                                        echo '  <li>' . $row['name'] . ': ' . $row['data'] . '</li>';
                                                    } else if ($row['f_type'] == 'check') {
                                                        if ($row['data'] == 'on')
                                                            echo '  <li>' . $row['name'] . ': Sim' . '</li>';
                                                        else {
                                                            echo '  <li>' . $row['name'] . ': Não' . '</li>';
                                                        }
                                                    } else if ($row['f_type'] == 'textarea') {
                                                        echo '  <li>' . $row['name'] . ': ' . $row['data'] . '</li>';
                                                    }
                                                }
                                            } ?>
                                </ul>

                            </div>
                            <div class="offset-md-3 col-md-3 col-sm-12 text-xs-center text-md-left">
                                <?php echo '<p><span class="text-muted">' . $this->lang->line('Invoice Date') . '  :</span> ' . dateformat($invoice['invoicedate']) . '</p> <p><span class="text-muted">' . $this->lang->line('Due Date') . ' :</span> ' . dateformat($invoice['invoiceduedate']) . '</p>  <p><span class="text-muted">' . $this->lang->line('Terms') . ' :</span> ' . $invoice['termtit'] . '</p><p><span class="text-muted">Série :</span> ' . $invoice['serie_name'] . '</p>'; ?>
                                <?php if ($invoice['status'] == 'canceled') { ?>
                                <p style="background-color:rgba(255, 0, 39, 0.22)"><strong class="pb-1"> Documento
                                        Anulado</strong></p>
                                <p style="background-color:rgba(255, 0, 39, 0.22)"><strong
                                            class="pb-1">Observações:</strong><?php echo $invoice['justification_cancel'];
                                    } ?></p>
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
                                            $sub_t += $row['subtotal'] + $row['totaldiscount'];
                                            $myArraytaxid = explode(";", $row['taxavals']);
                                            $valsum = 0;
                                            foreach ($myArraytaxid as $row1) {
                                                $valsum += $row1;
                                            }
                                            echo '<tr>
														<th scope="row">' . $c . '</th>
															<td><a href="' . base_url('products/edit?id=' . $row['pid']) . '"><strong class="invoice_a">' . $row['product'] . '</strong></a></td>											
															<td>' . amountExchange($row['price'], $invoice['multi'], $invoice['loc']) . '</td>
															 <td>' . amountFormat_general($row['qty']) . ' ' . $row['unit'] . '</td>
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
                                                    } else if ($row['f_type'] == 'check') {
                                                        if ($row['data'] == 'on')
                                                            $z_custom_fields .= $row['name'] . ': Sim<br>';
                                                        else {
                                                            $z_custom_fields .= $row['name'] . ': Não<br>';
                                                        }
                                                    } else if ($row['f_type'] == 'textarea') {
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
                                        <div class="col-md-8"><p
                                                    class="lead"><?php echo $this->lang->line('Payment Status') ?>:
                                                <u><strong id="pstatus"><?php echo $this->lang->line(ucwords($invoice['status'])) ?></strong></u>
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
												$myArraytaxperc = explode(";", $row['taxaperc']);
												$myArraytaxComo = explode(";", $row['taxacomo']);
                                                for ($i = 0; $i < count($myArraytaxname); $i++) {
                                                    $jatem = false;
                                                    for ($oo = 0; $oo < count($arrtudo); $oo++) {
                                                        if ($arrtudo[$oo]['title'] == $myArraytaxname[$i]) {
                                                            $arrtudo[$oo]['val'] = ($arrtudo[$oo]['val'] + $myArraytaxvals[$i]);
															$arrtudo[$oo]['inci'] = ($arrtudo[$oo]['inci']+$row['subtotal']);
															$jatem = true;
															
															if($arrtudo[$oo]['typ'] == '2'){
																$nameise = $this->common->withholdingsidname($myArraytaxComo[$i]);
																if($arrtudo[$oo]['nameise'] != $nameise){
																	$arrtudo[$oo]['val'] = $nameise;
																	$jatem = false;
																}else{
																	$arrtudo[$oo]['val'] = $arrtudo[$oo]['nameise'];
																	break;
																}
															}else{
																 break;
															}
                                                        }
                                                    }

                                                    if (!$jatem) {
														$nameiselog = '';
														$typ = '0';
														if($myArraytaxComo[$i] > 1){
															$typ = '2';
															$nameiselog = $this->common->withholdingsidname($myArraytaxComo[$i]);
														}else if($myArraytaxComo[$i] > 1){
															$typ = '1';
														}														
                                                        $stack = array('title' => $myArraytaxname[$i], 'val' => $myArraytaxvals[$i], 'perc'=>$myArraytaxperc[$i].' %', 'inci'=>$row['subtotal'], 'typ' => $typ, 'nameise' => $nameiselog);
                                                        array_push($arrtudo, $stack);
                                                    }
                                                }
                                            }

                                            for ($r = 0; $r < count($arrtudo); $r++) {
                                                echo "<tr>";
                                                echo "<td>" . $arrtudo[$r]['title'] . "</td>";
												if($arrtudo[$r]['typ'] == '2'){
													echo '<td class="text-xs-right">' . $arrtudo[$r]['nameise'] . '</td>';
												}else{
													echo '<td class="text-xs-right">' . amountExchange($arrtudo[$r]['val'], 0, $this->aauth->get_user()->loc) . '</td>';
												}
                                                
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

                        <!-- Invoice Footer -->
                        <?php if (is_array($custom_fields)) {
                            echo '<hr><div class="card">';
                            foreach ($custom_fields as $row) {
                                if ($row['f_type'] == 'text') { ?>
                                    <div class="row m-t-lg">
                                        <div class="col-md-10">
                                            <strong><?php echo $row['name'] ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $row['data'] ?>
                                        </div>
                                    </div>
                                <?php } else if ($row['f_type'] == 'check') { ?>
                                    <div class="row m-t-lg">
                                        <div class="col-md-10">
                                            <strong><?php echo $row['name'] ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php if ($row['data'] == 'on') echo 'Sim'; else 'Não' ?>
                                        </div>
                                    </div>
                                <?php } else if ($row['f_type'] == 'textarea') { ?>
                                    <div class="row m-t-lg">
                                        <div class="col-md-10">
                                            <strong><?php echo $row['name'] ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $row['data'] ?>
                                        </div>
                                    </div>
                                <?php }
                            }
                            echo '</div>';
                        } ?>
                        <?php
                        if ($invoice['expedition'] != null && $invoice['expedition'] != "") { ?>
                            <div id="invoice-footer"><p class="lead">Entrega e Transporte:</p>
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Expedição</th>
                                        <th>Viatura</th>
                                        <th>Data / Hora Início do Transporte</th>
                                        <th>Local da Carga</th>
                                        <th>Local da Descarga</th>
                                    </tr>
                                    </thead>
                                    <tbody id="activity">
                                    <?php
                                    echo '<tr>';
                                    echo '<td>';
                                    echo $invoice['exp_mat'];
                                    echo '</td>';
                                    echo '<td>';
                                    if ($invoice['autoid'] == 0) {
                                        echo $invoice['expd_name'];
                                    } else {
                                        echo '<a href="' . base_url('assests/edit?id=' . $invoice['autoid']) . '">' . $invoice['autoid_name'] . '</a>';
                                    }
                                    echo '</td>';
                                    echo '<td>' . $invoice['exp_date'] . '</td>';
                                    echo '<td>' . $invoice['charge_address'] . '<br>' . $invoice['charge_postbox'] . '<br>' . $invoice['charge_city'] . '<br>' . $invoice['charge_country_name'] . '<br>' . '</td>';
                                    echo '<td>' . $invoice['discharge_address'] . '<br>' . $invoice['discharge_postbox'] . '<br>' . $invoice['discharge_city'] . '<br>' . $invoice['discharge_country_name'] . '<br>' . '</td>';

                                    echo '<tr>';
                                    echo '<td colspan="5">' . $invoice['notes'] . '</td>';
                                    echo '</tr>';
                                    ?>

                                    </tbody>
                                </table>

                                <div class="row">

                                    <div class="col-md-7 col-sm-12">
                                        <h6><?php echo $this->lang->line('Terms & Condition') ?></h6>
                                        <?php echo '<br>' . $invoice['terms']; ?>
                                    </div>

                                </div>

                            </div>
                        <?php } ?>
                        <div id="invoice-footer"><p class="lead"><?php echo $this->lang->line('Credit Transactions') ?>
                                :</p>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('Date') ?></th>
                                    <th><?php echo $this->lang->line('Method') ?></th>
                                    <th><?php echo $this->lang->line('Debit') ?></th>
                                    <th><?php echo $this->lang->line('Credit') ?></th>
                                    <th><?php echo $this->lang->line('Note') ?></th>


                                </tr>
                                </thead>
                                <tbody id="activity">
                                <?php foreach ($activity as $row) {

                                    echo '<tr>
									<td><a href="view_payslip?id=' . $row['id'] . '&inv=' . $invoice['iid'] . '" class="btn btn-blue btn-sm"><span class="icon-print" aria-hidden="true"></span> ' . $this->lang->line('Print') . '  </a> ' . $row['date'] . '</td>
									<td>' . $row['methodname'] . '</td>
									  <td>' . amountExchange($row['debit'], 0, $this->aauth->get_user()->loc) . '</td>
									   <td>' . amountExchange($row['credit'], 0, $this->aauth->get_user()->loc) . '</td>
									<td>' . $row['note'] . '</td>
								</tr>';
                                } ?>

                                </tbody>
                            </table>

                            <div class="row">

                                <div class="col-md-7 col-sm-12">
                                    <h6><?php echo $this->lang->line('Terms & Condition') ?></h6>
                                    <p> <?php echo '<br>' . $invoice['terms']; ?></p>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        <h4>Documentos relacionados</h4>
                        <h6>O documento <?php echo $invoice['irs_type_n'] . ' ' . $invoice['irs_type_s'] . ' ' . $invoice['serie_name'] . '/' . $invoice['tid'] ?>
                            teve origem nos documentos abaixo (Está conciliado com)</h6>
                        <div class="row">
                            <table class="table table-striped">
                                <?php if (is_array($docs_origem)) {
                                    $reicoun = count($docs_origem);
                                    if ($reicoun > 0) {
                                        echo "<thead>
											<tr>
												<th>Documento</th>
												<th>Série/Nº</th>
												<th>Data Emissão</th>
												<th>NIF/NIC</th>
												<th>Ilíquido</th>
												<th>Impostos</th>
												<th>Total Liq.</th>
												<th>" . $this->lang->line('Settings') . "</th>
											</tr>
										</thead>
										<tbody id='activity'>";
                                        foreach ($docs_origem as $row) {
                                            $tiiid = $row['id'];
                                            echo '<tr>';
                                            echo "<td><strong>" . $row['tipo'] . "</strong></td>";
                                            echo "<td>" . $row['serie_name'] . '/' . $row['tid_doc'] . "</td>";
                                            echo "<td>" . $row['invoicedate'] . "</td>";
                                            echo "<td>" . $row['tax_id'] . "</td>";
                                            echo "<td>" . amountExchange($row['subtotal'], 0, $this->aauth->get_user()->loc) . "</td>";
                                            echo "<td>" . amountExchange($row['tax'], 0, $this->aauth->get_user()->loc) . "</td>";
                                            echo "<td>" . amountExchange($row['total'], 0, $this->aauth->get_user()->loc) . "</td>";
											$drafd = $row['draft'];
											$extd = $row['ext'];
                                            if ($row['type_related'] == "0" || $row['type_related'] == "2") {
                                                echo '<td><a href="' . base_url("invoices/view?id=$tiiid&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
													<a href="' . base_url("invoices/printinvoice?id=$tiiid&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            } else if ($row['type_related'] == "1") {
                                                echo '<td><a href="' . base_url("invoices/view?id=$tiiid&draf=0&ext=$extaa") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("invoices/printinvoice?id=$tiiid&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            } else if ($row['type_related'] == "3") {
                                                echo '<td><a href="' . base_url("quote/view?id=$tiiid&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("quote/printquote?id=$tiiid&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            } else if ($row['type_related'] == "13" || $row['type_related'] == "14") {
                                                echo '<td><a href="' . base_url("receipts/view?id=$tiiid&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("receipts/printinvoice?id=$tiiid&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            }
                                            echo '</tr>';
                                        }
                                        echo "</tbody>";
                                    } else {
                                        echo '<thead></thead><tbody id="activity"><tr><td>Não existe nenhum documento que tivesse origem neste documento!</td><tr></tbody>';
                                    }

                                } else {
                                    echo '<thead></thead><tbody id="activity"><tr><td>Não existe nenhum documento que tivesse origem neste documento!</td><tr></tbody>';
                                } ?>
                            </table>
                        </div>
                        <h6>O
                            documento <?php echo $invoice['irs_type_n'] . ' ' . $invoice['irs_type_s'] . ' ' . $invoice['serie_name'] . '/' . $invoice['tid'] ?>
                            deu origem aos documentos abaixo (Foi conciliado com)</h6>
                        <div class="row">
                            <table class="table table-striped">
                                <?php if (is_array($docs_deu_origem)) {
                                    $reicoun2 = count($docs_deu_origem);
                                    if ($reicoun2 > 0) {
                                        echo "<thead>
											<tr>
												<th>Documento</th>
												<th>Série/Nº</th>
												<th>Data Emissão</th>
												<th>NIF/NIC</th>
												<th>Ilíquido</th>
												<th>Impostos</th>
												<th>Total Liq.</th>
												<th>" . $this->lang->line('Settings') . "</th>
											</tr>
										</thead>
										<tbody>";
                                        foreach ($docs_deu_origem as $row) {
                                            $tiiide = $row['id'];
                                            echo '<tr>';
                                            echo "<td><strong>" . $row['tipo'] . "</strong></td>";
                                            echo "<td>" . $row['serie_name'] . '/' . $row['tid_doc'] . "</td>";
                                            echo "<td>" . $row['invoicedate'] . "</td>";
                                            echo "<td>" . $row['tax_id'] . "</td>";
                                            echo "<td>" . amountExchange($row['subtotal'], 0, $this->aauth->get_user()->loc) . "</td>";
											if($row['type_related'] == "13" || $row['type_related'] == "14")
											{
												echo "<td>" . amountExchange(0, 0, $this->aauth->get_user()->loc) . "</td>";
											}else{
												echo "<td>" . amountExchange($row['tax'], 0, $this->aauth->get_user()->loc) . "</td>";
											}
											echo "<td>" . amountExchange($row['total'], 0, $this->aauth->get_user()->loc) . "</td>";
											
											$drafd = $row['draft'];
											$extd = $row['ext'];
                                            if ($row['type_related'] == "0" || $row['type_related'] == "2") {
                                                if ($row['draft'] == "0") {
                                                    echo '<td><a href="' . base_url("invoices/view?id=$tiiide&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("invoices/printinvoice?id=$tiiide&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                                } else {
                                                    echo '<td><a href="' . base_url("invoices/view?id=$tiiide&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("invoices/printinvoice?id=$tiiide&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                                }
                                            } else if ($row['type_related'] == "1") {
                                                echo '<td><a href="' . base_url("invoices/view?id=$tiiide&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("invoices/printinvoice?id=$tiiide&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            } else if ($row['type_related'] == "3") {
                                                echo '<td><a href="' . base_url("quote/view?id=$tiiide&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("quote/printquote?id=$tiiide&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            } else if ($row['type_related'] == "13" || $row['type_related'] == "14") {
                                                echo '<td><a href="' . base_url("receipts/view?id=$tiiide&draf=$drafd&ext=$extd") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>
															<a href="' . base_url("receipts/printquote?id=$tiiide&draf=$drafd&ext=$extd") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> ';
                                            }

                                            echo '</tr>';
                                        }
                                        echo "</tbody>";
                                    } else {
                                        echo '<thead></thead><tbody id="activity"><tr><td>Não existe nenhum documento que desse origem a este documento!</td><tr></tbody>';
                                    }
                                } else {
                                    echo '<thead></thead><tbody id="activity"><tr><td>Não existe nenhum documento que desse origem a este documento!</td><tr></tbody>';
                                } ?>
                            </table>
                        </div>
                        <hr>
                        <h6>Outros Anexos</h6>
                        <div class="row">
                            <table class="table table-striped">
                                <thead>
                                </thead>
                                <tbody id="activity">
                                <?php foreach ($attach as $row) {

                                    echo '<tr><td><a data-url="' . base_url() . 'invoices/file_handling?op=delete&name=' . $row['col1'] . '&invoice=' . $invoice['iid'] . '" class="aj_delete"><i class="btn-danger btn-lg fa fa-trash"></i></a> <a class="n_item" href="' . base_url() . 'userfiles/attach/' . $row['col1'] . '"> ' . $row['col1'] . ' </a></td></tr>';
                                } ?>

                                </tbody>
                            </table>
                        </div>
                        <div class="card">
                            <pre>Allowed: gif, jpeg, png, docx, docs, txt, pdf, xls </pre>
                            <br>
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <div class="btn btn-success fileinput-button display-block">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>Select files...</span>
                                <!-- The file input field used as target for the file upload widget -->
                                <input id="fileupload" type="file" name="files[]" multiple>
                            </div>
                        </div>

                        <!-- The global progress bar -->
                        <div id="progress" class="progress progress-sm mt-1 mb-0">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- The container for the uploaded files -->
                        <table id="files" class="files table table-striped"></table>
                        <br>
                    </div>
                    <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
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

                                    echo '<tr><td>' . $row['note'] . '</td><td>' . $row['created'] . '</td></tr>';
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
        var url = '<?php echo base_url() ?>invoices/file_handling?id=<?php echo $invoice['iid'] ?>';
        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            formData: {'<?php echo $this->security->get_csrf_token_name()?>': crsf_hash},
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('#files').append('<tr><td><a data-url="<?php echo base_url() ?>invoices/file_handling?op=delete&name=' + file.name + '&invoice=<?php echo $invoice['iid'] ?>" class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>');
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
                                <?php echo $metodos_pagamentos; ?>
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
                                   value="Pagamento da Factura: #<?php echo $invoice['irs_type'] . ' ' . $invoice['serie'] . '/' . $invoice['tid']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" class="form-control required" name="tid" id="invoiceid"
                               value="<?php echo $invoice['iid'] ?>">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
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
