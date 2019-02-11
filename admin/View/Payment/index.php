<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<legend>Agency List</legend>

		<?php $flash = $this->Session->flash('success'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="payment_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>No.</th>
							<th>Payment ID</th>
							<th>Agency</th>
							<th>Date</th>
							<th>Type</th>
							<th>Status</th>
							<th>Payment name</th>
							<th>Card No.</th>
							<th>Card type</th>
							<th>Card amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if($payments): ?>
							<?php foreach ($payments as $key => $value): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td>
										<a target="_blank" href="https://sandbox.braintreegateway.com/merchants/tnnc2y3sq3ctj5cb/transactions/<?php echo $value['Payment']['payment_id']; ?>"><?php echo isset($value['Payment']['payment_id']) ? $value['Payment']['payment_id'] : '' ?></a>
									</td>
									<td>
										<a href="/admin/agency-detail/<?php echo $value['Agency']['id']; ?>"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></a>
									</td>
									<td><?php echo isset($value['Payment']['transaction_date']) ? $value['Payment']['transaction_date'] : '' ?></td>
									<td><?php echo isset($value['Payment']['type']) ? $value['Payment']['type'] : '' ?></td>
									<td> -- </td>
									<td><?php echo isset($value['Payment']['customer_name']) ? $value['Payment']['customer_name'] : '' ?></td>
									<td><?php echo isset($value['Payment']['card_no']) ? '************' . $value['Payment']['card_no'] : '' ?></td>
									<td><?php echo isset($value['Payment']['card_type']) ? $value['Payment']['card_type'] : '' ?></td>
									<td><?php echo isset($value['Payment']['amount']) ? $value['Payment']['amount'] : '' ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="/admin/js/jquery.dataTables.min.js"></script>
<script src="/admin/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#payment_list').DataTable({
			"order": [[ 0, 'desc' ]]
		});
	});
</script>