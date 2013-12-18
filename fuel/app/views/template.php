<?php

$logged_in = \Auth::check();
$user = $logged_in ? \Auth::instance()->get_user_array() : false;

function is_active($url, $add_class = true)
{
	return $url == Uri::string() ? ($add_class ? ' class="active"' : ' active') : '';
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.png">

    <title><?php echo $title; ?></title>

    <?php echo Asset::css("bootstrap.css"); ?>
    <?php echo Asset::css("default.css"); ?>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <?php echo Asset::js("html5shiv.js"); ?>
    <?php echo Asset::js("respond.min.js"); ?>
    <![endif]-->
  </head>

  <body>

    <!-- Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo htmlspecialchars(Uri::create('/')); ?>">Internverktøy BS</a>
          </div>
          <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
              <?php if ($user) { ?>
              <li<?php echo is_active("userlist"); ?>><a href="<?php echo htmlspecialchars(Uri::create('userlist')); ?>">Brukerliste</a></li>
              <li class="dropdown">
              	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Printer <b class="caret"></b></a>
              	<ul class="dropdown-menu">
              	  <li<?php echo is_active("printer/siste"); ?>><a href="<?php echo htmlspecialchars(Uri::create('printer/siste')); ?>">Siste utskrifter</a></li>
	                <li<?php echo is_active("printer/fakturere"); ?>><a href="<?php echo htmlspecialchars(Uri::create('printer/fakturere')); ?>">Fakturering</a></li>
	            </ul>
              <li<?php echo is_active("kalender"); ?>><a href="<?php echo htmlspecialchars(Uri::create('kalender')); ?>">Kalender</a></li>
	          </li>
              <?php } ?>
              <!--<li><a href="#contact">Contact</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li class="dropdown-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>-->
            </ul>
            <ul class="nav navbar-nav navbar-right">
            	<?php
            	if ($user)
            	{
            	?>
            	<li class="dropdown<?php echo is_active("userdetails", false); ?>">
            		<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo htmlspecialchars($user['realname']); ?> <b class="caret"></b></a>
            		<ul class="dropdown-menu">
            			<li><a href="<?php echo htmlspecialchars(Uri::create('userdetails')); ?>">Brukerinfo</a></li>
            			<li><a href="<?php echo htmlspecialchars(Uri::create('logout')); ?>">Logg ut</a></li>
            		</ul>
            	</li>
            	<?php
            	} else {
            		echo '
            	<li><a href="'.htmlspecialchars(Uri::create("login")).'">Logg inn</a></li>';
            	}
            	?>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>

      <!-- Begin page content -->

      <div class="container">

      	<?php echo implode("", Messages::get()); ?>

      	<div class="page-header">
          <h1><?php echo $title; ?></h1>
        </div>
        <?php echo $content; ?>
      </div>
    </div>

    <div id="footer" class="hidden-print">
      <div class="container">
        <p class="text-muted credit">Blindern Studenterhjem (<a href="/">offisiell side</a>) - Kontakt Henrik Steen ved forespørsler vedr. denne siden</p>
      </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <?php echo Asset::js("jquery-1.10.2.min.js"); ?>
    <?php echo Asset::js("bootstrap.min.js"); ?>
  </body>
</html>
