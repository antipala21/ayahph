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
			<div class="col-lg-12 col-xlg-12 col-md-12">
				<div class="product">
					<small>4111111111111210</small>
					<div id="dropin-container"></div>
					<button id="submit-button" class="btn btn-success">Submit</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="/agency/js/dropin.min.js"></script>
<script type="text/javascript">
	(function(){

		$(document).ready(function(){

			// Braintree
			var button = document.querySelector('#submit-button');

			braintree.dropin.create({
				authorization: 'sandbox_ypwwzxvh_tnnc2y3sq3ctj5cb',
				container: '#dropin-container'
			}, function (createErr, instance) {
				button.addEventListener('click', function () {
					instance.requestPaymentMethod(function (err, payload) {
						console.log('error ' +  JSON.stringify(err));
						console.log('payload ' +  JSON.stringify(payload));
						if (!err) {
							alert("Payment Success.");
							success_card();
						}
					});
				});
			});

			function success_card () {
				$.ajax({
					type: 'post',
					url: '/agency/account/success_card',
					data: {},
					error: function(){console.log('error')},
					success: function(res){
						console.log(res);
						location.reload();
					}
				});
			}

		}); // doc. ready
	})();
</script>