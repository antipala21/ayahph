<style type="text/css">

	.row.nurse-item {
		padding: 1em;
		margin: 25px;
		border:1px solid #d5dbdd;
		border-radius: 5px;
		box-shadow:0px 4px 2px rgba(0,0,0,0.04);
		background:#fff;
	}

	.row.nurse-item:hover {
		cursor: pointer;
	}

	.nurse-item-container {
		margin-top: 10px;
	}

	.page-wrapper {
		background: #FFF;
	}

	.media-right.align-self-center {
		text-align: center;
	}

	.agency-detail-container {
		margin-top: 25px;
	}

</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 agency-detail-container">
				<p><b>Agency Name: </b><?php echo isset($agency['name']) ? $agency['name'] : '-'; ?></p>
				<p><b>Agency Address: </b><?php echo isset($agency['address']) ? $agency['address'] : '-'; ?></p>
				<p><b>Agency Contact: </b><?php echo isset($agency['phone_number']) ? $agency['phone_number'] : '-'; ?></p>
			</div>
			<div class="col-md-12"><hr></div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<?php if ($nurse_maids): ?>
				<h3>Nursemaid List</h3>
				<?php foreach($nurse_maids as $key => $value): ?>
					<div class="row nurse-item">
						<div class="col-md-2">
							<div class="media-left align-self-center">
								<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
							</div>
						</div>
						<div class="col-md-8">
							<div class="media-body">
								<h4>
									<?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : ' ' ?>
									<?php echo isset($value['NurseMaid']['last_lname']) ? $value['NurseMaid']['last_lname'] : ' ' ?>
								</h4>
								<p><?php echo isset($value['NurseMaid']['self_introduction']) ? $value['NurseMaid']['self_introduction'] : ' ' ?></p>
							</div>
						</div>
						<div class="col-md-2">
							<div class="media-right align-self-center">
								<a href="/agency-nursemaid-detail/<?php echo isset($value['NurseMaid']['id']) ? $value['NurseMaid']['id'] : null; ?>" class="btn btn-info">View</a><hr>
								<a href="#" class="btn btn-info">Hire Now</a><hr>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php else: ?>
				<h3>Nursemaid List is empty.</h3>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>