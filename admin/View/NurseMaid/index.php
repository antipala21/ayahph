<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<legend>Nursemaid List</legend>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="nursemaid_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID.</th>
							<th>Name</th>
							<th>Agency Name</th>
							<th>Rating</th>
							<th>Gender</th>
							<th>Address</th>
							<th>Phone</th>
							<th>Status</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						<?php if($nurse_maids): ?>
							<?php foreach ($nurse_maids as $key => $value): ?>
								<tr>
									<td>
										<a href="/admin/nursemaid-detail/<?php echo $value['NurseMaid']['id']; ?>"><?php echo $value['NurseMaid']['id']; ?></a>
									</td>
									<td>
										<a href="/admin/nursemaid-detail/<?php echo $value['NurseMaid']['id']; ?>"><?php echo $value['NurseMaid']['first_name'] ?></a>
									</td>
									<td>
										<a href="/admin/agency-detail/<?php echo $value['Agency']['id']; ?>"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '' ?></a>
									</td>
									<td>
										<?php echo isset($value['NurseMaid']['rating']) ? round($value['NurseMaid']['rating'], 2) : 0 ?>
									</td>
									<td><?php echo isset($value['NurseMaid']['gender']) ? Configure::read('gender_array')[$value['NurseMaid']['gender']] : '' ?></td>
									<td>
										<?php echo isset($value['NurseMaid']['address']) ? $value['NurseMaid']['address'] : '' ?>
									</td>
									<td><?php echo isset($value['NurseMaid']['phone_number']) ? $value['NurseMaid']['phone_number'] : '' ?></td>
									<td><?php echo isset($value['NurseMaid']['status']) ? Configure::read('status')[$value['NurseMaid']['status']] : '' ?></td>
									<td><?php echo isset($value['NurseMaid']['created']) ? $value['NurseMaid']['created'] : '' ?></td>
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
		$('#nursemaid_list').DataTable({
			 "order": [[ 0, 'desc' ]]
		});
	});
</script>