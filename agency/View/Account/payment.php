<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<?php $payment_msg = $this->Session->flash('payment_msg'); ?>
			<?php if($payment_msg): ?>
				<div class="alert alert-danger">
					<?php echo $payment_msg; ?>
				</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-12 center">
				<?php echo $this->Form->create(false, 
					array(
						'id' => 'payment_form',
						'style' => '',
						'data-parsley-validate' => 'data-parsley-validate',
						'class' => 'form-horizontal form-label-left',
						// 'url' => array('controller' => 'Subscription', 'action' => 'index'),
					)); ?>

					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12" for="data[firstname]">Account Name <span class="required">*</span>
						</label>
						<?php
							echo $this->Form->input('firstname', array(
								'div' => array('class' => 'col-md-6 col-sm-6 col-xs-12'),
								'label' => false,
								'autofocus' => 'autofocus',
								'required' => true,
								'class' => 'form-control col-md-7 col-xs-12',
								'placeholder' => 'First Name',
								'value' => $this->Session->read('Auth.User.representative_name')
							));
						?>
					</div>

					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12" for="data[firstname]">Amount <span class="required">*</span>
						</label>
						<?php
							echo $this->Form->input('amount', array(
								'div' => array('class' => 'col-md-6 col-sm-6 col-xs-12'),
								'label' => false,
								'autofocus' => 'autofocus',
								'required' => true,
								'class' => 'form-control col-md-7 col-xs-12',
								'placeholder' => 'Amount',
								'value' => 500,
								'readonly' => true
							));
						?>
					</div>

					<div id="dropin-container"></div>
					<div class="ln_solid"></div>
					<small>4111111111111210</small>
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
							<button class="btn btn-primary reset" type="reset">Reset</button>
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
		</div>
	</div>
</div>

<!-- <script src="/agency/js/dropin.min.js"></script> -->
<script src="https://js.braintreegateway.com/js/braintree-2.31.0.min.js"></script>
<script type="text/javascript">
	(function(){

		$(document).ready(function(){

			// Braintree
			// var button = document.querySelector('#submit-button');

			// braintree.dropin.create({
			// 	authorization: 'sandbox_ypwwzxvh_tnnc2y3sq3ctj5cb',
			// 	container: '#dropin-container'
			// }, function (createErr, instance) {
			// 	button.addEventListener('click', function () {
			// 		instance.requestPaymentMethod(function (err, payload) {
			// 			console.log('error ' +  JSON.stringify(err));
			// 			console.log('payload ' +  JSON.stringify(payload));
			// 			if (!err) {
			// 				alert("Payment Success.");
			// 				success_card();
			// 			}
			// 		});
			// 	});
			// });

			$.ajax({
				url: '/agency/token',
				type: 'get',
				dataType: 'json',
				success: function (data) {
					braintree.setup(data, 'dropin', { 
						container : 'dropin-container',
						paypal: {
							singleUse: false,
							amount: 10.00,
							currency: 'USD'
						}
					});
				},
				error: function () {}
			});

		}); // doc. ready
	})();
</script>