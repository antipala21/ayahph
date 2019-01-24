
<style type="text/css">
	.profile-img{
		text-align: center;
	}
	.profile-img img{
		width: 70%;
		height: 100%;
	}
	.profile-img .file {
		position: relative;
		overflow: hidden;
		margin-top: -20%;
		width: 70%;
		border: none;
		border-radius: 0;
		font-size: 15px;
		background: #212529b8;
	}
	.profile-img .file input {
		position: absolute;
		opacity: 0;
		right: 0;
		top: 0;
	}
	.profile-head h5{
		color: #333;
	}
	.profile-head h6{
		/*color: #0062cc;*/
	}
	.profile-edit-btn{
		border: none;
		border-radius: 1.5rem;
		width: 70%;
		padding: 2%;
		font-weight: 600;
		color: #6c757d;
		cursor: pointer;
	}
	.proile-rating{
		font-size: 12px;
		color: #818182;
		margin-top: 5%;
	}
	.proile-rating span{
		color: #495057;
		font-size: 15px;
		font-weight: 600;
	}
	.profile-head .nav-tabs{
		margin-bottom:5%;
	}
	.profile-head .nav-tabs .nav-link{
		font-weight:600;
		border: none;
	}
	.profile-head .nav-tabs .nav-link.active{
		border: none;
		border-bottom:2px solid #0062cc;
	}
	.profile-work{
		padding: 14%;
		margin-top: -15%;
	}
	.profile-work p{
		font-size: 12px;
		color: #818182;
		font-weight: 600;
		margin-top: 10%;
	}
	.profile-work a{
		text-decoration: none;
		color: #495057;
		font-weight: 600;
		font-size: 14px;
	}
	.profile-work ul{
		list-style: none;
	}
	.profile-tab label{
		font-weight: 600;
	}
	.profile-tab p{
		font-weight: 600;
		color: #0062cc;
	}
	.container-fluid {
		padding-top: 1em;
	}
	#myTabContent .row {
		padding-top: 1em;
		padding-bottom: 1em;
	}
	.profile-head {
		padding-top: 3em;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12">

				<?php echo $this->Form->create('User', 
					array(
						'id' => 'user-editrequirements',
						'style' => '',
						'class' => 'form-horizontal form-label-left',
						'enctype'=>'multipart/form-data'
					));
				?>

				<div class="item form-group">

					<?php $haspermit = 0; ?>
					<?php $fileName = isset($user['User']['business_permit_url']) ? $user['User']['business_permit_url'] : ''; ?>

					<img style="width: 25%; <?php echo isset($fileName) && !empty($fileName) ? '' : 'display: none'; ?>" src="<?php echo myTools::checkHost() . '/img/business_permits/' . $fileName ; ?>" id="test">

					
					<hr>
					<label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Valid User ID</label>
					<?php echo $this->Form->input('User.business_permit_url', array(
							'type' => 'file',
							'autofocus' => 'autofocus',
							'legend' => false,
							'label' => false,
							'required' => $haspermit ? false : true,
							'div' => array('class' => 'col-md-6 col-sm-6 col-xs-12'),
							'class' => 'form-control col-md-7 col-xs-12',
							'id' => 'business_permit',
					)); ?>
				</div>

				<hr>
				<div class="col-md-4">
					<div class="form-group">
						<div class="control-field">
							<div class="col-sm-offset-2 col-sm-10 center">
								<button type="submit" class="btn btn-primary" id="btn_update">Update</button>
							</div>
						</div>
					</div>
				</div>

				<?php echo $this->Form->end(); ?>

			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	
	$(document).ready(function(){
		$('#business_permit').change(function(){
			var fileName = $(this).val();
			if(fileName !== null) {
				console.log(fileName);
				readURL(this, '#permit');
			}
		});

		function readURL(input, el) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function (e) {
					$(el).attr('href', e.target.result);
					$(el).show();

					$('img#test').attr('src', e.target.result);
					$('img#test').show();
				}

				reader.readAsDataURL(input.files[0]);
			}
		}

		$('a#permit').click(function(e){
			e.preventDefault();
			viewImage($(this).attr('href'));
		});
	});

</script>