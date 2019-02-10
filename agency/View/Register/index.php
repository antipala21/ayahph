<link rel="stylesheet" href="/agency/fonts/material-design-iconic-font/css/material-design-iconic-font.css">
<link href="/agency/css/style_wizard.css" rel="stylesheet" type="text/css">

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
				<h3 class="text-themecolor">Agency Registration</h3>
			</div>
		</div>

		<?php echo $this->Form->create('Agency', 
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
							Agency Name *
						</label>
						<?php echo $this->Form->input('name', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Agency Name
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Agency Address *
						</label>
						<?php echo $this->Form->input('address', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Agency Address
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Agency Representative Name *
						</label>
						<?php echo $this->Form->input('representative_name', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Agency Representative Name
						</div>
					</div>
					<div class="form-row">
						<label for="">
							Phone Number *
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
							Description *
						</label>
						<?php echo $this->Form->input('description', array(
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
						<div class="invalid-feedback">
							Please provide Description
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
<script src="/agency/js/jquery.steps.js"></script>
<script type="text/javascript">
	$(function(){

		var email_thru_flg = false;

		$(document).ready(function(){
			$('#AgencyEmail').focusout(function(){
				var usersEmail = this.value;
				$.ajax({
					type: 'POST',
					url: '/agency/checkEmail',
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
			return emailReg.test( $email );
		}

		$(document).keypress(function(e) {
			if(e.which == 13) {
				e.preventDefault();
			}
		});

		function invalidateInput (el, text = false) {
			$(el).css('border-color', '#dc3545');
			$(el).next().css('display', 'block');
			if (text) {
				$(el).next().text(text);
			}
		}

		var successStep =  false;

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
					var _email = $('#AgencyEmail').val().trim();
					var _emailConfirm = $('#AgencyEmailConfirm').val().trim();

					// email validtion
					if (_email != '') {
						if (!validateEmail(_email)) {
							invalidateInput('#AgencyEmail');
							return false;
						}
						else if (!email_thru_flg) {
							invalidateInput('#AgencyEmail', 'Email is already taken.');
							return false;
						}
					} else {
						invalidateInput('#AgencyEmail');
						return false;
					}

					// Email confirm validation
					if (_emailConfirm == '' || _emailConfirm != _email) {
						invalidateInput('#AgencyEmailConfirm');
						return false;
					}

					// Password validation
					var _password = $('#AgencyPassword').val().trim();
					var _passwordConfirm = $('#AgencyPasswordConfirm').val().trim();

					if (_password == '') {
						invalidateInput('#AgencyPassword');
						return false;
					}

					if (_password != _passwordConfirm) {
						invalidateInput('#AgencyPasswordConfirm');
						return false;
					}
				}

				// Agency Info Validation
				if (currentIndex == 1) {
					if ($('#AgencyName').val().trim() == '') {
						invalidateInput('#AgencyName');
						return false;
					}

					if ($('#AgencyAddress').val().trim() == '') {
						invalidateInput('#AgencyAddress');
						return false;
					}

					if ($('#AgencyRepresentativeName').val().trim() == '') {
						invalidateInput('#AgencyRepresentativeName');
						return false;
					}

					if ($('#AgencyPhoneNumber').val().trim() == '') {
						invalidateInput('#AgencyPhoneNumber');
						return false;
					}

					if ($('#AgencyDescription').val().trim() == '') {
						invalidateInput('#AgencyDescription');
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

	}); // end js
</script>