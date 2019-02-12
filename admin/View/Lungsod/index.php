<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<legend>Address List</legend>

		<?php $flash = $this->Session->flash('success'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>
		<a class="btn btn-success" href="/admin/municipal/add">Add</a>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="lungsod_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Search Key</th>
							<th>Status</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						<?php if($lungsod): ?>
							<?php foreach ($lungsod as $key => $value): ?>
								<tr>
									<td><?php echo $value['Lungsod']['id']; ?></td>
									<td><?php echo $value['Lungsod']['name']; ?></td>
									<td><?php echo $value['Lungsod']['search_key']; ?></td>
									<td>Active</td>
									<td><?php echo $value['Lungsod']['created']; ?></td>
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
		$('#lungsod_list').DataTable({
			"order": [[ 0, 'desc' ]]
		});
	});
</script>