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
				<table id="agency_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>No.</th>
							<th>Name</th>
							<th>Representative name</th>
							<th>Address</th>
							<th>Phone number</th>
							<th>Status</th>
							<th>Start date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($agencies): ?>
							<?php foreach ($agencies as $key => $value): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td>
										<a href="/admin/agency-detail/<?php echo $value['Agency']['id']; ?>"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></a>
									</td>
									<td><?php echo isset($value['Agency']['representative_name']) ? $value['Agency']['representative_name'] : '' ?></td>
									<td><?php echo isset($value['Agency']['address']) ? $value['Agency']['address'] : '' ?></td>
									<td><?php echo isset($value['Agency']['phone_number']) ? $value['Agency']['phone_number'] : '' ?></td>
									<td><?php echo isset($value['Agency']['status']) && $value['Agency']['status'] ? 'Available' : 'Not Available' ?></td>
									<td><?php echo isset($value['Agency']['created']) ? $value['Agency']['created'] : '' ?></td>
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
		$('#agency_list').DataTable();
	});
</script>