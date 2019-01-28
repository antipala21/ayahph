<link href="/agency/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Schedules </h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Schedule list</li>
				</ol>
			</div>
		</div>
		<h2>Schedules</h2>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="schedule_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>User Name</th>
							<th>Comment</th>
							<th>User Phone</th>
							<th>Address</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($schedules): ?>
							<?php foreach ($schedules as $key => $value): ?>
								<tr>
									<td>
										<a href="schedule/detail/<?php echo $value['Transaction']['id']; ?>"><?php echo $value['Transaction']['id']; ?></a>
									</td>
									<td><?php echo isset($value['User']['display_name']) ? $value['User']['display_name'] : '' ?></td>
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
		$('#schedule_list').DataTable();
	});
</script>