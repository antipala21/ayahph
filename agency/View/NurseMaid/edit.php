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

	.blocker {
		z-index: 60;
	}

	.modal a.close-modal {
		top: 1.5px;
		right: 0.5px;
	}

	/************START CROP***********/

	.profile-img .file {
		position: relative;
		overflow: hidden;
		/*margin-top: -20%;*/
		width: 80%;
		border: none;
		border-radius: 0;
		font-size: 15px;
		background: #212529b8;
		padding: 4px;
	}

	.profile-img{
		text-align: center;
	}
	.profile-img img{
		width: 70%;
		height: 100%;
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
	.modal a.close-modal {
		top: 1.5px;
		right: 0;
	}

</style>

<link rel="stylesheet" href="/agency/css/jquery-ui.css">
<link rel="stylesheet" href="/agency/css/cropper.min.css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<div class="row nurse-item">
					<div class="col-md-2">
						<div class="media-left align-self-center">
							<div class="profile-img">
								<?php if(isset($nurse_maid['image_url']) && !empty($nurse_maid['image_url'])): ?>
									<img class="rounded-circle" id="view-profile-pic" src="<?php echo myTools::getProfileImgSrcAgency($nurse_maid['image_url']); ?>" alt="Avatar">
								<?php else: ?>
									<img class="rounded-circle" id="view-profile-pic" src="https://randomuser.me/api/portraits/women/50.jpg">
								<?php endif; ?>
								<div class="file btn btn-lg btn-primary" id="trigger_input_file">
									Change
								</div>
								<?php echo $this->Form->input('NurseMaid.image_url', array(
											'type' => 'file',
											'label' => false,
											'required' => false,
											'div' => false,
											'id' => 'ProfileImage',
											'style' => 'display:none'
									)); ?>
							</div>
							<center>
								<button class="btn btn-primary hide" id="save_image">Save Image</button>
							</center>
						</div>
					</div>
					<div class="col-md-8">
					<?php echo $this->Form->create('NurseMaid', 
							array(
								'id' => 'nurseMaidAcountUpdate',
								'style' => '',
							)); ?>
						<div class="media-body">
							<?php echo $this->Form->hidden('id', array('value' => $nurse_maid['id'])); ?>
							<h4>First Name</h4>
								<?php echo $this->Form->input('first_name', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['first_name']
									)); ?>
							<h4>Midle Name</h4>
									<?php echo $this->Form->input('middle_name', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['middle_name']
									)); ?>
							<h4>Last Name</h4>
									<?php echo $this->Form->input('last_lname', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['last_lname']
									)); ?>
							<h4>Self Introduction</h4>
								<?php echo $this->Form->input('self_introduction', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['self_introduction']
									)); ?>
							<h4>Address</h4>
								<?php echo $this->Form->input('address', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['address']
									)); ?>
							<h4>Gender</h4>
								<?php echo $this->Form->input('gender', array(
									'options' => Configure::read('gender_array'),
									'required' => true,
									'label' => false,
									'div'=> false,
									'class'=>'form-control',
									'value' => $nurse_maid['gender']
								)); ?>
							<h4>Birthday</h4>
								<?php echo $this->Form->input('birthdate', array(
									'type' => 'text',
									'required' => true,
									'label' => false,
									'div'=> false,
									'autocomplete' => 'off',
									'class'=>'form-control',
									'value' => $nurse_maid['birthdate']
								)); ?>
							<h4>Marital status</h4>
								<?php echo $this->Form->input('marital_status', array(
									'options' => Configure::read('marital_status'),
									'required' => true,
									'label' => false,
									'div'=> false,
									'autocomplete' => 'off',
									'class'=>'form-control',
									'value' => $nurse_maid['marital_status']
								)); ?>
							<h4>Phone number</h4>
								<?php echo $this->Form->input('phone_number', array(
									'type' => 'text',
									'required' => true,
									'label' => false,
									'div'=> false,
									'autocomplete' => 'off',
									'class'=>'form-control',
									'value' => $nurse_maid['phone_number']
								)); ?>
							<h4>NO. of years experience</h4>
								<?php echo $this->Form->input('years_experience', array(
									'type' => 'text',
									'required' => true,
									'label' => false,
									'div'=> false,
									'autocomplete' => 'off',
									'class'=>'form-control',
									'value' => $nurse_maid['years_experience']
								)); ?>
						</div>
					</div>
					<div class="col-12 nurse-item-container">
						<div class="media-right align-self-center">
							<button type="submit" class="btn btn-info">Save</button>
							<hr>
						</div>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


<a href="#image_cropper_modal" id="trigger_image_cropper_modal" class="btn btn-info" rel="modal:open"></a>
<div class="modal hide" id="image_cropper_modal">
	<div class="popup">
		<h2>Crop image</h2>
		<a class="close" href="#">&times;</a>
		<div class="content">
			<div class="custom_modal-body">
				<div class="img-container">
					<img id="modal-image" src="" alt="Picture" style="max-width: 640px; max-height: 480px;">
				</div>
			</div>
			<div class="custom_modal-footer">
				<br>
				<center>
					<button type="button" class="btn btn-info" id="upload-image">CROP</button>
				</center>
			</div>
		</div>
	</div>
</div>

<script src="/agency/js/jquery-ui.js"></script>
<script src="/agency/js/cropper.min.js"></script>

<script type="text/javascript">
(function(){

	$(document).ready(function() {

		// Date Picker
		$("#NurseMaidBirthdate").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});

		/**
		 * start cropper
		 */
		var cropper;
		var dataUrl = null;


		$('#trigger_input_file').click(function () {
			$('#ProfileImage').val('');
			$('#ProfileImage').click();
		});

		$('.close').click(function(){
			cropper.destroy();
			$('#popup1').hide();
		});

		$('#ProfileImage').change(function(){
			console.log('change');

			var fileName = $(this).val();

			console.log(fileName);

			if(fileName !== null) {
				// $('#crop_image').modal('show');
				$('#trigger_image_cropper_modal').click();
				readURL(this);
			}

		});

		// read image data url
		function readURL(input) {

			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('img#modal-image').attr('src', e.target.result);
					triggerCopping();
				}
				reader.readAsDataURL(input.files[0]);
			}
		}

		// trigger the cropping of the image.
		function triggerCopping () {

			var image = document.getElementById('modal-image');
			var button = document.getElementById('upload-image');

			var croppable = false;

			var $toCrop = $('.img-container > img');

			cropper = new Cropper(image, {
				aspectRatio: 4 / 4,
				allowSelect: false,
				allowResize: false,
				autoCropArea: 0.65,
				viewMode: 1,
				built: function () {
					toCrop.cropper("setCropBoxData", { width: "600px", height: "600px" });
				},
				ready: function () {
					croppable = true;
				}
			});

			button.onclick = function () {
				var croppedCanvas;
				var roundedCanvas;
				var roundedImage;
				if (!croppable) {
					return;
				}
				// Crop
				croppedCanvas = cropper.getCroppedCanvas();
				// Round
				roundedCanvas = getCanvas(croppedCanvas);
				// Show
				roundedImage = document.getElementById('view-profile-pic');
				roundedImage.src = roundedCanvas.toDataURL();
				dataUrl = roundedCanvas.toDataURL();
				$('#save_image').show();
				$('.close-modal').click();
				cropper.destroy();
				$('#popup1').hide();

				$('#save_image').show();
			};
		}

		function getCanvas(sourceCanvas) {
			var canvas = document.createElement('canvas');
			var context = canvas.getContext('2d');
			var width = 600; //sourceCanvas.width;
			var height = 600; //sourceCanvas.height;

			canvas.width = width;
			canvas.height = height;

			context.imageSmoothingEnabled = true;
			context.drawImage(sourceCanvas, 0, 0, width, height);
			context.globalCompositeOperation = 'destination-in';
			context.beginPath();
			context.fill();

			return canvas;
		}

		var HOST = window.location.host;

		$('#save_image').click(function(){
			var nurse_maid_id = "<?php echo isset($nurse_maid['id']) ? $nurse_maid['id'] : null; ?>";
			$.ajax({
				type: 'post',
				url: '/agency/account/ajax_nursemaid_image_upload',
				data: {
					"profile-image" : dataUrl,
					"nurse_maid_id" : nurse_maid_id
				},
				error: function(){console.log('error')},
				success: function(res){
					console.log(res);
					alert('Image Updated.');
					location.reload();
				}
			});
		});

		$('.upload_btn_close').click(function(){
			$('#success_upload').modal('hide');
		});


	}); // end doc ready.

})();
</script>