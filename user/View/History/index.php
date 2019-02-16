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
				<h3>- History -</h3>
			</div>
			<div class="col-md-12"><hr></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table id="history_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>Agency Name</th>
							<th>Nursemaid Name</th>
							<th>Rate</th>
							<th>Comment</th>
							<th>Schedule date</th>
							<th>Finish date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($history): ?>
							<?php foreach ($history as $key => $value): ?>
								<tr>
									<td>
										<a href="user/history/detail/<?php echo $value['Transaction']['id']; ?>"><?php echo $value['Transaction']['id']; ?></a>
									</td>
									<td>
										<a href="agency-detail/<?php echo $value['Agency']['id']; ?>"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></a>
									</td>
									<td>
										<a href="agency-nursemaid-detail/<?php echo $value['NurseMaid']['id']; ?>"><?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : '' ?></a>
									</td>
									<td>
										<?php echo $value['Rating']['rate']; ?>
									</td>
									<td>
										<?php echo $value['Rating']['comment']; ?>
									</td>
									<td>
										<?php echo isset($value['Transaction']['transaction_start']) ? date('Y-m-d h:i: a', strtotime($value['Transaction']['transaction_start'])) : '' ?>
									</td>
									<td>
										<?php echo  date('Y-m-d h:i: a', strtotime($value['Transaction']['modified'])); ?>
									</td>
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
		$('#history_list').DataTable({
			"order": [[ 0, 'desc' ]]
		});
	});
</script>