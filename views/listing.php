<?php
// dump( get_defined_vars() );die;
$pages = '';

$debug = FALSE;

if ($data['paginate'] == 'yes') :

    $numofpages = (int) $data['pages'];
    $page = (int) $data['current'];
    $alwaysShowPages = array(1, 2, 3);
    $pagination_links = '';
    // dynamically add last 3 pages
    for ($i = $numofpages; $i >= $numofpages - 2; $i--) {
        $alwaysShowPages[] = $i;
    }

    for ($i = 1; $i <= $numofpages; $i++) :
        $showPageLink = true;

        if ($numofpages > 10 && !in_array($i, $alwaysShowPages)) {
            if (($i < $page && ($page - $i) > 3)
                || ($i > $page && ($i - $page) > 3)
            ) {
                $showPageLink = false;
            }
        }

        $dotDotdot = isset($dotDotdot) ? $dotDotdot : FALSE;
        if ($showPageLink) {
            if ($i == $page) {
                $pagination_links .= "<li class=\"page-item active\"><a class=\"page-link\" >{$i}</a></li>";
            } else {
                $pagination_links .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?pagenum={$i}\">{$i}</a></li>";
            }
        } elseif ($dotDotdot == FALSE) {
            $pagination_links .= "<li class=\"page-item disabled\"><a class=\"page-link\" >...</a></li>";
            $dotDotdot = TRUE;
        }

    endfor;


    $pages = '<ul class="pagination">' . PHP_EOL;
    $pages .= $pagination_links;
    $pages .= "</ul>" . PHP_EOL;


endif;
?>

<div class="row sort-bar" style="margin-bottom: 20px;">
    <div class="col-sm-12 col-md-6" style="margin-top: 10px;">
        <label for="sort">Sort by : </label>
        <?php $category = $data['category']; ?>
        <a href="?cat=<?=$category?>&sort=points">Popular</a>&nbsp;|&nbsp;<a href="?cat=<?=$category?>&sort=distance">Distance</a>
    </div>
    <div class="col-sm-12 col-md-6">
        <label for="subcats">Category: </label>
        <?php echo $data['cats']; ?>
    </div>


</div>
<?php foreach ($data as $row) : ?>
    <?php

    if (!isset($row->id)) continue;

    $row->img  = $row->images->images;
    foreach ($row->img as &$img) {
        $img = $img;
    }
    //$row->link = '?listing=' . $row->id;
    //    $row->link = '/gsplace/' . str_replace( array( ' ', '-' ), '+', $row->name );
    $slug      = make_slug_id($row->name, $row->id);
    $row->link = '/gsplace/' . $slug;
    $row->img  = is_array($row->img) ? array_shift($row->img) : $row->img;
    $row->img  = 'https://grandstrandapi.com/photos/' . $row->img;

    $row->description = ('' == $row->description) ? '' : $row->description;
    $row->formattedAddress = "{$row->address}<br>{$row->city}, {$row->state} {$row->zip}";
    $rating = $row->rating / 2;
    $rate = get_stars($row->rating, $row->points, 'long');
    ?>

    <div class="hotel listing row">
        <div class="col-sm-12 col-md-5 col-lg-4 ">
            <div class="image relative">
                <a class="link-wrap" href="<?php echo $row->link; ?>">
                    <div class="img-wrap">
                        <img src="<?php echo $row->img; ?>" alt="<?php echo $row->name; ?>" title="<?php echo $row->name; ?>">
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-12 col-md-7 col-lg-8">
            <a class="link-wrap" href="<?php echo $row->link; ?>">
                <h3 class="head-28 blue"><?php echo $row->name; ?></h3>
            </a>
            <div class="row">
                <div class="col-sm-12 col-lg-8">
                    <div><?php echo $rate; ?></div>
                    <!--
                <a class="link-wrap" href="<?php echo $row->link; ?>">
                    <div class="rating-holder orange">
                        <div class="rating-stars">
                            <i class="i-starfull icon-28"></i> <i class="i-starfull icon-28"></i> <i class="i-starfull icon-28"></i> <i class="i-starfull icon-28"></i> <i class="i-starhalf icon-28"></i>
                        </div>
                        <div class="rating-reviews">from 88<br>reviews</div>
                        <div class="clear"></div>
                    </div>
                    <p>rating review?</p>
                </a>
                -->
                    <div class="font-12 blue">
                        <p><?php echo $row->formattedAddress; ?></p>
                    </div>
                    <div class="font-12 blue">
                        <p><a href="tel://<?php echo $row->fphone; ?>"><?php echo $row->fphone; ?></a></p>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-4" style="padding-left: 0; padding-right: 0;">
                    <div id="link-bar-new" class="wide center">
                        <a class="track-deals" href="<?php echo $row->link; ?>" rel="nofollow">
                            <div class="col-sm-12">
                                <button class="button-sm wide info-back"><i class="fa fa-info" aria-hidden="true"></i>&nbsp;&nbsp;Info </button>
                            </div>
                        </a>
                        <?php if ('' != $row->website) : ?>
                            <a class="track-deals" href="<?php echo $row->website; ?>" rel="nofollow" target="_blank">
                                <div class="col-sm-12">
                                    <button class="button-sm wide blue-back"><i class="fa fa-globe" aria-hidden="true"></i> Website </button>
                                </div>
                            </a>
                        <?php endif; ?>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="font-12 blue">
                        <p><?php echo $row->description; ?></p>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

<?php endforeach; ?>
<?php

echo $pages;

if (isset($_GET['debug']) && $_GET['debug'] == 'dump' || $debug == TRUE) {
    dump($data);
}

?>