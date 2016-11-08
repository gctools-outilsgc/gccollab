<?php
/**
 * WET 4 Site Branding
 * 
 */

// footer
//echo elgg_view('core/account/login_dropdown');
$site_url = elgg_get_site_url();

// cyu - strip off the "GCconnex" branding bar for the gsa
if (strcmp('gsa-crawler',strtolower($_SERVER['HTTP_USER_AGENT'])) != 0) {
?>


    <div id="app-brand">
        <div class="container">
            <div class="row">
                <div class="col-sm-3 col-xs-12">
                    <div class="app-name">
                    <a href="<?php echo $site_url; ?>">
                        <span><span class="bold-gc">GC</span>collab</span>
                    </a>
                    </div>
                    
                    
                </div>
                <div class="col-md-6 col-sm-4 col-xs-hidden">
                 <?php echo elgg_view('search/search_box', $vars); ?>
                </div>
                <div class="col-md-3 col-sm-5 col-xs-12">
                <?php echo elgg_view('page/elements/topbar_wrapper', $vars);?>
                </div>
            </div>
        </div>
        
    </div>

<?php } ?>
