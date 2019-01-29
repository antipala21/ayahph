<link rel="stylesheet" href="/user/fonts/material-design-iconic-font/css/material-design-iconic-font.css">
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
							Please provide an email.
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
							Name *
						</label>
						<?php echo $this->Form->input('name', array(
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
						<h1>Finish!</h1>
					</div>
				</section>
			</div>
		<?php echo $this->Form->end(); ?>
	</div>
</div>
<script src="/user/js/jquery.steps.js"></script>
<script src="/js/dropin.min.js"></script>
<script type="text/javascript">
	$(function(){

		function validateEmail($email) {
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			return emailReg.test( $email );
		}

		$(document).keypress(function(e) {
			if(e.which == 13) {
				e.preventDefault();
			}
		});

		function invalidateInput (el) {
			$(el).css('border-color', '#dc3545');
			$(el).next().css('display', 'block');
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
					var _email = $('#UserEmail').val().trim();
					var _emailConfirm = $('#UserEmailConfirm').val().trim();

					// email validtion
					if (_email != '') {
						if (!validateEmail(_email)) {
							invalidateInput('#UserEmail');
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
					if ($('#UserName').val().trim() == '') {
						invalidateInput('#UserName');
						return false;
					}

					if ($('#UserAddress').val().trim() == '') {
						invalidateInput('#UserAddress');
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

	}); // end js
</script>