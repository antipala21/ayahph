<link href="/agency/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Transactions </h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Transaction list</li>
				</ol>
			</div>
		</div>
		<h2>Hire Request</h2>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="transaction_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>User Name</th>
							<th>Comment</th>
							<th>User Phone</th>
							<th>Address</th>
							<th>Schedule</th>
						</tr>
					</thead>
					<tbody>
						<?php if($transactions): ?>
							<?php foreach ($transactions as $key => $value): ?>
								<tr>
									<td>
										<a href="transaction/detail/<?php echo $value['Transaction']['id']; ?>"><?php echo $value['Transaction']['id']; ?></a>
									</td>
									<td>
										<a href="/agency/user/<?php echo $value['User']['id'] ?>"><?php echo $value['User']['display_name'] ?></a>
									</td>
									<td><?php echo isset($value['Transaction']['comment']) ? $value['Transaction']['comment'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['user_phone_number']) ? $value['Transaction']['user_phone_number'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['user_address']) ? $value['Transaction']['user_address'] : '' ?></td>
									<td><?php echo isset($value['Transaction']['transaction_start']) ? date('Y-m-d h:i:sa', strtotime($value['Transaction']['transaction_start'])) : '' ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="/agency/js/jquery.dataTables.min.js"></script>
<script src="/agency/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#transaction_list').DataTable({
			 "order": [[ 0, 'desc' ]]
		});
	});
</script>