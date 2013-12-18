<?php

$login_error = implode("", \Messages::get());

?>

<div class="row">
	<div class="col-md-6">

<p>Du logger inn med brukernavnet og passordet du har på printeren/wikien.</p>
<p><a href="https://p.blindern-studenterhjem.no/request.php">Be om tilgang</a></p>

	</div>
	<div class="col-md-6">

<?php echo Form::open(array("class" => "form-horizontal", "role" => "form")); ?>

<!--<div class="panel panel-default">
	<div class="panel-body">-->

	<?php if (isset($login_error)): ?>
		<div class="error"><?php echo $login_error; ?></div>
	<?php endif; ?>

	<div class="form-group">
		<label for="form_username" class="col-lg-2 control-label">Brukernavn</label>
		<div class="col-lg-10">
			<?php echo Form::input('username', Input::post('username'), array("placeholder" => "Brukernavn", "class" => "form-control")); ?>
		</div>
	</div>

	<div class="form-group">
		<label for="form_password" class="col-lg-2 control-label">Passord</label>
		<div class="col-lg-10">
			<?php echo Form::password('password', '', array("placeholder" => "Passord", "class" => "form-control")); ?>
		</div>
	</div>

	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<div class="checkbox">
				<label>
					<?php echo Form::checkbox("remember_me", null, true); ?> Forbli pålogget
				</label>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<?php echo Form::submit(array('value'=>'Logg inn', "name" => "submit", "class" => "btn btn-default")); ?>
		</div>
	</div><!--
	</div>
</div>--><!-- end panel-->

	</div> <!-- end col -->
</div>

<?php echo Form::close(); ?>