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
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<br>
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
												<div class="sl-left"> <img src="/user/images/users/1.jpg" alt="user" class="img-circle"> </div>
												<div class="sl-right">
													<div><a href="#" class="link"><?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : '-' ?></a>
														<p><?php echo isset($value['Agency']['short_description']) ? $value['Agency']['short_description'] : '-' ?></p>
														<div class="row">
															<div class="col-md-8 m-b-20">
																<p class="m-t-30"><?php echo isset($value['Agency']['description']) ? $value['Agency']['description'] : '-' ?></p>
															</div>
															<div class="col-md-4 m-b-20">
																<!-- <img src="/user/images/big/img4.jpg" alt="user" class="img-responsive radius">
																<img src="/user/images/big/img4.jpg" alt="user" class="img-responsive radius">
																<img src="/user/images/big/img4.jpg" alt="user" class="img-responsive radius">
																<img src="/user/images/big/img4.jpg" alt="user" class="img-responsive radius"> -->
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
										<h4 class="font-medium m-t-30">Reviews</h4>
										<hr>
										<div class="like-comm"> <a href="javascript:void(0)" class="link m-r-10">2 comment</a> <a href="javascript:void(0)" class="link m-r-10"><i class="fa fa-heart text-danger"></i> 5 Love</a> </div>
									</div>
								</div>

								<!-- Nurse maid list -->
								<div class="tab-pane" id="nursemaidlist<?php echo $id;?>" role="tabpanel">
									<div class="card-block">
										<h3>Nursemaid Detail</h3>
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

<script src="/js/bootstrap-notify.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		// $.notify({
		// 	icon: 'https://randomuser.me/api/portraits/med/men/77.jpg',
		// 	title: 'Byron Morgan',
		// 	message: 'Momentum reduce child mortality effectiveness incubation empowerment connect.'
		// },{
		// 	type: 'minimalist',
		// 	delay: 5000,
		// 	icon_type: 'image',
		// 	template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
		// 		'<img data-notify="icon" class="img-circle pull-left">' +
		// 		'<span data-notify="title">{1}</span>' +
		// 		'<span data-notify="message">{2}</span>' +
		// 	'</div>'
		// });
	});
</script>