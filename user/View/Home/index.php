<style type="text/css">
	.alert-minimalist {
		background-color: rgb(241, 242, 240);
		border-color: rgba(149, 149, 149, 0.3);
		border-radius: 3px;
		color: rgb(149, 149, 149);
		padding: 10px;
	}
	.alert-minimalist > [data-notify="icon"] {
		height: 50px;
		margin-right: 12px;
	}
	.alert-minimalist > [data-notify="title"] {
		color: rgb(51, 51, 51);
		display: block;
		font-weight: bold;
		margin-bottom: 5px;
	}
	.alert-minimalist > [data-notify="message"] {
		font-size: 80%;
	}
	.form-group.sort_key {
		width: 300px;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<div>
			<?php echo $this->Form->input('Sort', array(
				'options' => Configure::read('sort_key'),
				'label' => 'Sort By ',
				'class' => 'form-control',
				'id' => 'sort_key',
				'div' => array('class' => 'form-group sort_key'),
				'empty' => '---',
				'default' => isset($get['order']) ? $sort_value[$get['order']] : ''
			)); ?>
		</div>
		<!-- Row -->
		<div class="row">
			<?php if($agencies): ?>
				<?php foreach ($agencies as $key => $value): ?>
					<?php
						$id = isset($value['Agency']['id']) ? $value['Agency']['id'] : null;
					?>
					<div class="col-lg-12 col-xlg-12 col-md-12">
						<div class="card">
							<!-- Nav tabs -->
							<ul class="nav nav-tabs profile-tab" role="tablist">
								<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home<?php echo $id;?>" role="tab">Details</a> </li>
								<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile<?php echo $id;?>" role="tab">Profile</a> </li>
								<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#nursemaidlist<?php echo $id;?>" role="tab">Nursemaid</a> </li>
							</ul>
							<!-- Tab panes -->
							<div class="tab-content">

								<!-- Details -->
								<div class="tab-pane active" id="home<?php echo $id;?>" role="tabpanel">
									<div class="card-block">
										<div class="profiletimeline">
											<div class="sl-item">
												<div class="sl-left"> <img src="<?php echo myTools::getProfileImgSrcAgency($value['Agency']['image_url']); ?>" alt="user" class="img-circle"> </div>
												<div class="sl-right">
													<div><a href="#" class="link"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '-' ?></a>
														<p><?php echo isset($value['Agency']['short_description']) ? $value['Agency']['short_description'] : '-' ?></p>
														<div class="row">
															<div class="col-md-8 m-b-20">
																<p class="m-t-30"><?php echo isset($value['Agency']['description']) ? $value['Agency']['description'] : '-' ?></p>
															</div>
														</div>
														<div class="row">
															<div class="col-md-4 m-b-20">
																<a href="/agency-detail/<?php echo $id; ?>" class="btn btn-info">View Agency</a>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<h4 class="font-medium m-t-30">Reviews</h4>
										<hr>
										<div class="like-comm"> <a href="javascript:void(0)" class="link m-r-10"><?php echo isset($value['Agency']['rating_count']) ? $value['Agency']['rating_count'] : 0; ?> comment</a> <a href="javascript:void(0)" class="link m-r-10">
											<i class="fa fa-star text-warning" aria-hidden="true"></i> <?php echo round($value['Agency']['rating'], 2) ?></a>
										</div>
									</div>
								</div>

								<!--profile tab-->
								<div class="tab-pane" id="profile<?php echo $id;?>" role="tabpanel">
									<div class="card-block">
										<div class="row">
											<div class="col-md-3 col-xs-6 b-r"> <strong>Agency Name</strong>
												<br>
												<p class="text-muted"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '-' ?></p>
											</div>
											<div class="col-md-3 col-xs-6 b-r"> <strong>Mobile</strong>
												<br>
												<p class="text-muted"><?php echo isset($value['Agency']['phone_number']) ? $value['Agency']['phone_number'] : '-' ?></p>
											</div>
											<div class="col-md-3 col-xs-6 b-r"> <strong>Email</strong>
												<br>
												<p class="text-muted"><?php echo isset($value['Agency']['email']) ? $value['Agency']['email'] : '-' ?></p>
											</div>
											<div class="col-md-3 col-xs-6"> <strong>Location</strong>
												<br>
												<p class="text-muted"><?php echo isset($value['Agency']['address']) ? $value['Agency']['address'] : '-' ?></p>
											</div>
										</div>
										<hr>
										<p class="m-t-30"><?php echo isset($value['Agency']['description']) ? $value['Agency']['description'] : '-' ?></p>
										<div class="row">
											<div class="col-md-4 m-b-20">
												<a href="/agency-detail/<?php echo $id; ?>" class="btn btn-info">View Agency</a>
											</div>
										</div>
									</div>
								</div>

								<!-- Nurse maid list -->
								<div class="tab-pane" id="nursemaidlist<?php echo $id;?>" role="tabpanel">
									<div class="card-block">
										<a href="/agency-nursemaid/<?php echo $value['Agency']['id']; ?>">Nursemaid Detail</a>
										<br><br>
										<ul>
											<li><p>Total nurse maid (<?php echo isset($value['Agency']['total_nursemaid']) ? $value['Agency']['total_nursemaid'] : '0' ?>)</p></li>
											<li><p>Male (<?php echo isset($value['Agency']['male_nursemaid']) ? $value['Agency']['male_nursemaid'] : '0' ?>)</p></li>
											<li><p>Female (<?php echo isset($value['Agency']['female_nursemaid']) ? $value['Agency']['female_nursemaid'] : '0' ?>)</p></li>
											<li><p>Current Available (<?php echo isset($value['Agency']['current_available']) ? $value['Agency']['current_available'] : '0' ?>)</p></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- End Col -->
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function(){
		$(document).ready(function(){

			// Sort Trigger
			$('#sort_key').change(function(){
				window.location.href = "/?order=" + $("#sort_key option:selected" ).text();
			});

		});
	})();
</script>