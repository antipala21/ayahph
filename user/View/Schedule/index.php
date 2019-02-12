<link href="/agency/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<style type="text/css">
	.shedules-detail-container {
		margin-top: 25px;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 shedules-detail-container">
				<h3>- Schedules -</h3>
			</div>
			<div class="col-md-12"><hr></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table id="schedule_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>Agency Name</th>
							<th>User Phone</th>
							<th>Address</th>
							<th>Schedule</th>
						</tr>
					</thead>
					<tbody>
						<?php if($schedules): ?>
							<?php foreach ($schedules as $key => $value): ?>
								<tr>
									<td>
										<a href="schedule/detail/<?php echo $value['Transaction']['id']; ?>"><?php echo $value['Transaction']['id']; ?></a>
									</td>
									<td><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></td>
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
		$('#schedule_list').DataTable({
			 "order": [[ 0, 'desc' ]]
		});
	});
</script>