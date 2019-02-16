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

	.blocker {
		z-index: 60;
	}

	.modal a.close-modal {
		top: 1.5px;
		right: 0;
	}

	div#ui-datepicker-div {
		z-index: 99 !important;
	}

</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 agency-detail-container">
				<h3>- Announcements -</h3>
			</div>
			<div class="col-md-12"><hr></div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<?php if ($announcements): ?>
				<?php foreach($announcements as $key => $value): ?>
					<div class="row nurse-item">
						<div class="col-md-2">
							<div class="media-left align-self-center">
								<img style="width: 100%" class="rounded-circle" src="<?php echo myTools::getProfileImgSrcAgency($value['Agency']['image_url']); ?>">
							</div>
						</div>
						<div class="col-md-8">
							<div class="media-body">
								<h4>
									<?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : ' ' ?>
								</h4>
								<small>- Announcement -</small>
								<br><br>
								<p>****</p>
								<p><?php echo isset($value['Announcement']['content']) ? $value['Announcement']['content'] : ' ' ?></p>
								<p>****</p>
							</div>
						</div>
						<div class="col-md-2">
							<small><?php echo date('Y-m-d h:i:sa', strtotime($value['Announcement']['created'])) ?></small>
							<?php if ($value['Announcement']['status'] == 1): ?>
							<a href="/admin/announcement/update/<?php echo $value['Announcement']['id'] . '/0'; ?>" class="btn btn-danger">Hide</a>
							<?php else: ?>
								<a href="/admin/announcement/update/<?php echo $value['Announcement']['id'] . '/1'; ?>" class="btn btn-info">Show</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<?php else: ?>
				<h3>Announcement List is empty.</h3>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>