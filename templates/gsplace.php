<?php
/**
 * Template Name: GSPlace
 *
 * @package GSAPI_Connector
 *
 */

echo '<h2>Defined Variables</h2>';
$v = get_defined_vars();
var_export($v);
die;

$debug = TRUE;
get_header();


$data = $GLOBALS['gsapi_connector']->_fetch_detail();
$data = is_array($data) ? array_shift($data) : $data;
$rate = get_stars($data->rating, $data->points);
$mapAddress = '';
if (isset($data->address) && isset($data->city) && isset($data->state) && isset($data->zip)) :
	$address = str_replace(' ', '+', $data->address);
	$city    = str_replace(' ', '+', $data->city);
	$mapAddress = $address . ',' . $city . ','. $data->state . ','. $data->zip;
endif;
function go_away_extra_sidebar()
{
	unregister_sidebar('Footer');
}

add_action('after_setup_theme', 'go_away_extra_sidebar');

?>

<?php if (isset($data->error)) : ?>
	<div id="error_msg" class="">
		<a href="#" class="notice_close"></a>
		<div class="notice_content">
			<p><?php echo $data->error; ?></p>
		</div>
	</div>
<?php else : ?>
	<div id="content" class="container" style="border: 1px solid red">
		<div class="row">
			<div class="main col-lg-12 col-md-12" role="main">
				<h1 class="head-72 blue"><?php echo $data->name; ?></h1>
				<div class="address-bar">
					<i class="fa fa-map-marker"></i> &nbsp;<?php echo "{$data->address}, {$data->city}, SC {$data->zip}"; ?>
					<span class="phone-hide">&nbsp;&nbsp;&nbsp;</span>
					<span class="phone-show"></span> <i class="fa fa-phone"></i> &nbsp;
					<a href="tel: <?php echo $data->phone; ?>"><?php echo $data->fphone; ?></a> &nbsp;
					<span style="font-size:18px"><?php echo $rate; ?></span> from <?php echo $data->votes; ?> votes

				</div>
			</div>
		</div>
		<div class="row" style="margin:0 auto;">
			<?php if ($data->phone) : ?>
				<div class="col-xs-6 col-md-3"><a href="tel:<?php echo $data->phone; ?>"><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; phone </button></a></div>
			<?php endif; ?>
			<?php if ($data->website) : ?>
				<div class="col-xs-6 col-md-3"><a href="<?php echo $data->website; ?>" <?php if ($data->claimed <= 2) :
																																							echo ' rel="nofollow"';
																																							endif; ?>><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; website </button></a></div>
			<?php endif; ?>
			<?php if ($data->menulinks->{'menu-mobile'} || $data->menulinks->{'menu-web'}) : ?>
				<div class="col-xs-6 col-md-3"><a href="<?
																								if ($data->menulinks->{'menu-mobile'}) :
																									echo $data->menulinks->{'menu-mobile'};
																								else :
																									echo $data->menulinks->{'menu-web'};
																								endif; ?>" target="_blank" rel="nofollow"><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; food menu </button></a></div>
			<?php endif; ?>
			<?php if (isset($data->events) && $data->events) : ?>
				<div class="col-xs-6 col-md-3"><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; events </button></div>
			<?php endif; ?>
			<?php if (isset($data->reservation) && $data->reservation) : ?>
				<div class="col-xs-6 col-md-3"><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; reserve </button></div>
			<?php endif; ?>
			<?php if (isset($data->booking) && $data->booking) : ?>
				<div class="col-xs-6 col-md-3"><button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; booking </button></div>
			<?php endif; ?>
			<?php if ($mapAddress != '') : ?>
			<div class="col-xs-6 col-md-3"><button data-lightbox data-lity data-lity-target="//maps.google.com/maps?q=<?=$mapAddress?>" class=" button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp; directions </button></div>
			<?php endif; ?>
		</div>

		<div class="row">
			<div class="main col-lg-9 col-md-8" role="main">

				<?php if ($data->description != '') : ?>

					<div class="section-inner narrow">

						<h1 class="head-28 blue" style="margin-top:20px;">About <?php echo $data->name; ?></h1>

						<?php if ($data->images->logo) :
							echo '<img src="https://grandstrandapi.com/photos/' . $data->images->logo[0] . '" class="placelogo">';
						endif; ?>

						<?php if ((isset($data->description) && isset($data->description->long)) && $data->description->long) :
							echo $data->description->long;
								else :
							echo $data->description->short;
						endif; ?>

						<?php if (isset($data->tagline) && $data->tagline) :
							echo '<p class="tagline">' . $data->tagline . '</p>';
						endif; ?>
					</div>


					<?php
					if ((isset($data->images) && isset($data->images->images)) && $data->images->images) :
						//$allimg = array_merge($data->images->images, $data->images->logo);
						$pics = count($data->images->images);
						if ($pics >= 3) :
					?>

							<div class="responsive" style="margin:6px;">
								<?php foreach ($data->images->images as $image) :
									$image = preg_replace('/_l./i', '_m.', $image);
									$image = fixDoubleDottedExtensions($image);
									echo '<div style="min-height: 250px; max-height: 275px; background: url(https://grandstrandapi.com/photos/' . $image . ') center center; background-size: cover; "></div>';
								endforeach; ?>
							</div>
						<?php
						elseif ($pics == 1) : ?>

							<div class="row" style="margin:6px;">
								<?php

								$image = preg_replace('/_l./i', '_m.', $data->images->images[0]);
								$image = fixDoubleDottedExtensions($image);
								echo '<div class="col-sm-8" style="min-height: 250px; max-height: 275px; background: url(https://grandstrandapi.com/photos/' . $image . ') center center; background-size: cover; margin: 0 auto;"></div>'; ?>
							</div>
						<?php
						else : ?>

							<div class="row" style="margin:6px;">
								<?php
								foreach ($data->images->images as $image) :
									$image = preg_replace('/_l./i', '_m.', $image);
									$image = fixDoubleDottedExtensions($image);
									echo '<div class="col-sm-12 col-md-6" style="min-height: 250px; max-height: 275px; background: url(https://grandstrandapi.com/photos/' . $image . ') center center; background-size: cover; "></div>';
								endforeach; ?>
							</div>
					<?php endif; ?>

				<?php endif; ?>

			<?php endif; ?>

			<?php endif; ?>



<?php

		function fixDoubleDottedExtensions($image) {
			return str_replace(['..jpg', '..jpeg', '..png', '..gif'], ['.jpg', '.jpeg', '.png', '.gif'], $image);
		}
		if ($debug == TRUE) :
			//echo "<pre>"; print_r($data); echo "</pre>";
			dump($data);  // Lame cause this clutters the screen. Dump it to the console instead out of the way.
			//@TODO: You should include the Sumfony Debug Package since it can output to to the console, screen, or wherever.
			echo "<script>console.log(" . json_encode($data) . ")</script>";
		endif;
?>


		</div>