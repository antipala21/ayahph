<link rel="stylesheet" href="/user/fonts/material-design-iconic-font/css/material-design-iconic-font.css">
<link rel="stylesheet" href="/user/css/jquery-ui.css">
<link href="/css/style_wizard.css" rel="stylesheet" type="text/css">

<div class="page-wrapper" style="min-height: 818px; margin-right: 240px;">
	<div class="container-fluid">

		<?php $flash = $this->Session->flash('user-detail'); ?>
		<?php if($flash): ?>
			<div class="alert alert-warning">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">User Registration</h3>
			</div>
		</div>

		<?php echo $this->Form->create('User', 
				array(
					'id' => 'agencyRegistration',
					'style' => '',
				)); ?>
			<div id="wizard">
				<!-- SECTION 1 -->
				<h4></h4>
				<section>
					<div class="form-row">
						<label for="">
						Email *
						</label>
						<?php echo $this->Form->input('email', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide a valid email.
						</div>
					</div>
					<div class="form-row">
						<label for="">
						Confirm Email *
						</label>
						<?php echo $this->Form->input('email_confirm', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Confirm email is invalid.
						</div>
					</div>

					<div class="form-row">
						<label for="">
						Password *
						</label>
						<?php echo $this->Form->input('password', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide a password.
						</div>
					</div>
					<div class="form-row">
						<label for="">
						Confirm Password *
						</label>
						<?php echo $this->Form->input('password_confirm', array(
							'type' => 'password',
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Confirm password is invalid.
						</div>
					</div>
				</section>
				
				<!-- SECTION 2 -->
				<h4></h4>
				<section>
					<div class="form-row">
						<label for="">
							First Name *
						</label>
						<?php echo $this->Form->input('fname', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Name
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Address *
						</label>
						<?php echo $this->Form->input('address', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Address
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Birthday *
						</label>
						<?php echo $this->Form->input('birthdate', array(
							'type' => 'text',
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Brithday
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Gender *
						</label>
						<?php echo $this->Form->input('gender', array(
							'options' => Configure::read('gender_array'),
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
							'empty' => '--'
						)); ?>
						<div class="invalid-feedback">
							Please provide Gender
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Phone number *
						</label>
						<?php echo $this->Form->input('phone_number', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Phone number
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Display Name *
						</label>
						<?php echo $this->Form->input('display_name', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide display name
						</div>
					</div>
				</section>

				<!-- SECTION 3 -->
				<h4></h4>
				<section>
					<div class="product">
						<h3>Almost Done!</h3>
						<p>Next step, payment and legal document.</p>
						<p>Guidelines and Agreement....</p>
					</div>
				</section>
			</div>
		<?php echo $this->Form->end(); ?>
	</div>
</div>
<script src="/user/js/jquery-ui.js"></script>
<script src="/user/js/jquery.steps.js"></script>
<script src="/js/dropin.min.js"></script>
<script type="text/javascript">
	$(function(){

		var email_thru_flg = false;

		$(document).ready(function(){
			$('#UserEmail').focusout(function(){
				var usersEmail = this.value;
				$.ajax({
					type: 'POST',
					url: '/checkEmail',
					data: {
						email : usersEmail
					},
					success: function(data){
						var res = JSON.parse(data);
						if (res.result) {
							email_thru_flg = false;
						} else {
							email_thru_flg = true;
						}
					},
					error: function(error){
						console.log(error);
						email_thru_flg = false;
					}
				});
			});
		});

		function validateEmail($email) {
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			return emailReg.test($email);
		}

		$(document).keypress(function(e) {
			if(e.which == 13) {
				e.preventDefault();
			}
		});

		function invalidateInput (el, text = false) {
			$(el).focus();
			$(el).css('border-color', '#dc3545');
			$(el).next().css('display', 'block');
			if (text) {
				$(el).next().text(text);
			}
		}

		var successStep =  false;

		$('#UserEmail').keyup(function(){
			console.log('test');
		});

		$("#wizard").steps({
			headerTag: "h4",
			bodyTag: "section",
			transitionEffect: "fade",
			enableAllSteps: true,
			transitionEffectSpeed: 500,
			onStepChanging: function (event, currentIndex, newIndex) {

				successStep =  false;

				$('input').css('border', '1px solid #e6e6e6');
				$('.invalid-feedback').hide();

				if (currentIndex == 0) {
					var _email = $('#UserEmail').val().trim();
					var _emailConfirm = $('#UserEmailConfirm').val().trim();

					// email validtion
					if (_email != '') {
						if (!validateEmail(_email)) {
							invalidateInput('#UserEmail', 'Please provide a valid email.');
							return false;
						}
						else if (!email_thru_flg) {
							invalidateInput('#UserEmail', 'Email is already taken.');
							return false;
						}
					} else {
						invalidateInput('#UserEmail');
						return false;
					}

					// Email confirm validation
					if (_emailConfirm == '' || _emailConfirm != _email) {
						invalidateInput('#UserEmailConfirm');
						return false;
					}

					// Password validation
					var _password = $('#UserPassword').val().trim();
					var _passwordConfirm = $('#UserPasswordConfirm').val().trim();

					if (_password == '') {
						invalidateInput('#UserPassword');
						return false;
					}

					if (_password != _passwordConfirm) {
						invalidateInput('#UserPasswordConfirm');
						return false;
					}
				}

				// Agency Info Validation
				if (currentIndex == 1) {
					if ($('#UserFname').val().trim() == '') {
						invalidateInput('#UserFname');
						return false;
					}

					if ($('#UserAddress').val().trim() == '') {
						invalidateInput('#UserAddress');
						return false;
					}

					if ($('#UserBirthdate').val().trim() == '') {
						invalidateInput('#UserBirthdate');
						return false;
					}

					if ($('#UserGender').val().trim() == '') {
						invalidateInput('#UserGender');
						return false;
					}

					if ($('#UserPhoneNumber').val().trim() == '') {
						invalidateInput('#UserPhoneNumber');
						return false;
					}

					if ($('#UserDisplayName').val().trim() == '') {
						invalidateInput('#UserDisplayName');
						return false;
					}
				}

				if ( newIndex === 1 ) {
					$('.steps ul').addClass('step-2');
				} else {
					$('.steps ul').removeClass('step-2');
				}
				if ( newIndex === 2 ) {
					$('.steps ul').addClass('step-3');
				} else {
					$('.steps ul').removeClass('step-3');
				}

				if ( newIndex === 3 ) {
					$('.steps ul').addClass('step-4');
					$('.actions ul').addClass('step-last');
				} else {
					$('.steps ul').removeClass('step-4');
					$('.actions ul').removeClass('step-last');
				}

				successStep = true;
				return true;
			},
			onFinishing: function (event, currentIndex) {
				$('#agencyRegistration').submit();
				console.log('finishing');
			},
			onFinished: function (event, currentIndex) {
				// form.submit();
				console.log('finished na jud.');
			},
			labels: {
				finish: "Finish",
				next: "Next",
				previous: "Previous"
			}
		});
		// Custom Steps Jquery Steps
		$('.wizard > .steps li a').click(function(){
			if (successStep) {
				$(this).parent().addClass('checked');
				$(this).parent().prevAll().addClass('checked');
				$(this).parent().nextAll().removeClass('checked');
			}
		});
		// Custom Button Jquery Steps
		$('.forward').click(function(){
			$("#wizard").steps('next');
		})
		$('.backward').click(function(){
			$("#wizard").steps('previous');
		})
		// Checkbox
		$('.checkbox-circle label').click(function(){
			$('.checkbox-circle label').removeClass('active');
			$(this).addClass('active');
		});

		$("#UserBirthdate").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});


	}); // end js
</script>