<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Transactions</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
					<li class="breadcrumb-item active">Transactions list</li>
				</ol>
			</div>
		</div>

		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="transactions-list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>No.</th>
							<th>Transaction ID</th>
							<th>Agency id</th>
							<th>Nursemaid id</th>
							<th>User id</th>
							<th>Comment</th>
							<th>Phone number</th>
							<th>Status</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						<?php if($transactions): ?>
							<?php foreach ($transactions as $key => $value): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td><?php echo isset($value['Transaction']['id']) ? $value['Transaction']['id'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['agency_id']) ? $value['Transaction']['agency_id'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['nurse_maid_id']) ? $value['Transaction']['nurse_maid_id'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['user_id']) ? $value['Transaction']['user_id'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['comment']) ? $value['Transaction']['comment'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['user_phone_number']) ? $value['Transaction']['user_phone_number'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['status']) && $value['Transaction']['status'] ? 'Accepted' : 'Decline' ?></td>
									<td><?php echo isset($value['Transaction']['created']) ? $value['Transaction']['created'] : '' ?></td>
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
		$('#transactions-list').DataTable();
	});
</script>