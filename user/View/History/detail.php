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

	*{
		margin: 0;
		padding: 0;
	}
	.rate {
		float: left;
		height: 46px;
		padding: 0 10px;
	}
	.rate:not(:checked) > input {
		position:absolute;
		top:-9999px;
	}
	.rate:not(:checked) > label {
		float:right;
		width:1em;
		overflow:hidden;
		white-space:nowrap;
		cursor:pointer;
		font-size:30px;
		color:#ccc;
	}
	.rate:not(:checked) > label:before {
		content: '★ ';
	}
	.rate > input:checked ~ label {
		color: #ffc700;    
	}
	.rate:not(:checked) > label:hover,
	.rate:not(:checked) > label:hover ~ label {
		color: #deb217;  
	}
	.rate > input:checked + label:hover,
	.rate > input:checked + label:hover ~ label,
	.rate > input:checked ~ label:hover,
	.rate > input:checked ~ label:hover ~ label,
	.rate > label:hover ~ input:checked ~ label {
		color: #c59b08;
	}

	[type="radio"]:not(:checked) + label:before,
	[type="radio"]:not(:checked) + label:after {
		border: none;
	}
	[type="radio"]:checked + label:after, [type="radio"].with-gap:checked + label:after {
		background: none;
	}
	[type="radio"]:checked + label:after, [type="radio"].with-gap:checked + label:before, [type="radio"].with-gap:checked + label:after {
		border: none;
	}
	[type="radio"]:not(:checked) + label {
		padding-left: 35px;
	}

/* Modified from: https://github.com/mukulkant/Star-rating-using-pure-css */

</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 agency-detail-container">
				<h3>- History Detail -</h3>
			</div>
			<div class="col-md-12"><hr></div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				
				
					<div class="row nurse-item">
						<div class="col-md-2">
							<div class="media-left align-self-center">
								<img style="width: 100%" class="rounded-circle" src="<?php echo myTools::getProfileImgSrcAgency($value['NurseMaid']['image_url']); ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="media-body">
								<h4>
									<?php echo isset($value['Agency']['name']) ? $value['Agency']['name'] : ' ' ?>
								</h4>
								<p>Nursemaid Name:
									<small> <?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : ' ' ?> , <?php echo isset($value['NurseMaid']['last_lname']) ? $value['NurseMaid']['last_lname'] : ' ' ?></small>
								</p>
								<br>
								<p><?php echo isset($value['NurseMaid']['phone_number']) ? $value['NurseMaid']['phone_number'] : ' ' ?></p>
								<p><?php echo isset($value['NurseMaid']['address']) ? $value['NurseMaid']['address'] : ' ' ?></p>
							</div>
						</div>
						<div class="col-md-4">
							<div class="stars">
								<?php echo $this->Form->create('Rating', 
										array(
											'id' => 'user-rating',
											'style' => '',
											'class' => 'form-horizontal form-label-left',
											// 'url' => '/supplier/rate_supplier'
										));
									?>

								<?php echo $this->Form->hidden('nurse_maid_id', array('value' => $value['NurseMaid']['id'])); ?>
								<?php echo $this->Form->hidden('agency_id', array('value' => $value['Agency']['id'])); ?>
								<?php echo $this->Form->hidden('transaction_id', array('value' => $value['Transaction']['id'])); ?>
								<?php echo $this->Form->hidden('rating_id', array('value' => $value['Rating']['id'])); ?>

								<div class="rate">
									<input type="radio" id="star5" name="rate" value="5" />
									<label for="star5" title="5 Star"> </label>
									<input type="radio" id="star4" name="rate" value="4" />
									<label for="star4" title="4 Star"> </label>
									<input type="radio" id="star3" name="rate" value="3" />
									<label for="star3" title="3 Star"> </label>
									<input type="radio" id="star2" name="rate" value="2" />
									<label for="star2" title="2 Star"> </label>
									<input type="radio" id="star1" name="rate" value="1" />
									<label for="star1" title="1 Star"> </label>
								</div>

								<?php echo $this->Form->input('comment', array(
									'required' => true,
									'label' => false,
									'div'=> false,
									'class'=>'form-control',
									'value' => $value['Rating']['comment']
								)); ?>
								<hr>
								<button class="btn btn-success" type="submit" id="btn_rate_submit">Update</button>
							<?php echo $this->Form->end(); ?>
							</div>
						</div>
						<div class="col-md-2">
							<small><?php echo date('Y-m-d h:i:sa', strtotime($value['Transaction']['created'])) ?></small>
						</div>
					</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function(){

		var _rate = <?php echo $value['Rating']['rate'] ?>;

		$(document).ready(function(){
			if (_rate == 5) {
				$('#star5').click();
			} else if (_rate == 4) {
				$('#star4').click();
			} else if (_rate == 3) {
				$('#star3').click();
			} else if (_rate == 2) {
				$('#star2').click();
			} else if (_rate == 1) {
				$('#star1').click();
			}
		});
	})();
</script>