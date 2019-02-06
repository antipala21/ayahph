<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<legend>Nurse Maid Raing List</legend>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="nursemaid_ratings_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID.</th>
							<th>User Name</th>
							<th>Agency Name</th>
							<th>Nursemaid Name</th>
							<th>Comment</th>
							<th>Rate</th>
							<th>Status</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						<?php if($nursemaid_ratings): ?>
							<?php foreach ($nursemaid_ratings as $key => $value): ?>
								<tr>
									<td>
										<a href="/admin/nursemaid_raing-detail/<?php echo $value['NurseMaidRating']['id']; ?>"><?php echo $value['NurseMaidRating']['id']; ?></a>
									</td>
									<td>
										<a href="/admin/agency-detail/<?php echo $value['Agency']['id']; ?>"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></a>
									</td>
									<td>
										<a href="/admin/user-detail/<?php echo $value['User']['id']; ?>"><?php echo isset($value['User']['display_name']) ? $value['User']['display_name'] : '' ?></a>
									</td>
									<td>
										<?php echo $value['Nursemaid']['first_name'] ?>
									</td>
									<td><?php echo isset($value['NurseMaidRating']['comment']) ? $value['NurseMaidRating']['comment'] : '' ?></td>
									<td><?php echo isset($value['NurseMaidRating']['rate']) ? $value['NurseMaidRating']['rate'] : '' ?></td>
									<td><?php echo isset($value['NurseMaidRating']['status']) && $value['NurseMaidRating']['status'] ? 'Available' : 'Not Available' ?></td>
									<td><?php echo isset($value['NurseMaidRating']['created']) ? $value['NurseMaidRating']['created'] : '' ?></td>
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
		$('#nursemaid_ratings_list').DataTable();
	});
</script>