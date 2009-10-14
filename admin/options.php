<?php
/**
 * Updates our options
 * @package now-watching
 */

if ( !empty($_POST['update']) ) {
    require '../../../../wp-config.php';

    if ( !current_user_can('level_9') )
        die ( __('Cheating, huh?') );

    check_admin_referer('now-watching-update-options');

    $_POST = stripslashes_deep($_POST);

    $append = '';

	$options['AWSAccessKeyId']          = trim($_POST['AWSAccessKeyId']);
    $options['SecretAccessKey']         = trim($_POST['SecretAccessKey']);
    $options['formatDate']		= trim($_POST['format_date']);
    $options['associate']		= trim($_POST['associate']);
    $options['domain']			= trim($_POST['domain']);
    $options['debugMode']		= trim($_POST['debug_mode']);
    $options['useModRewrite']           = trim($_POST['use_mod_rewrite']);
    $options['menuLayout']		= ( trim($_POST['menu_layout']) == 'single' ) ? NW_MENU_SINGLE : NW_MENU_MULTIPLE;
    $options['proxyHost']		= trim($_POST['proxy_host']);
    $options['proxyPort']		= trim($_POST['proxy_port']);
    $options['moviesPerPage']            = trim($_POST['movies_per_page']);
    $options['permalinkBase']           = trim($_POST['permalink_base']);
    $options['multiuserMode']           = trim($_POST['multiuser_mode']);

    $nw_url->load_scheme($options['menuLayout']);

    switch ( $_POST['image_size'] ) {
        case 'Small':
        case 'Medium':
        case 'Large':
            $options['imageSize'] = $_POST['image_size'];
            break;
        default:
            $append .= '&imagesize=1';
            $options['imageSize'] = 'Medium';
            break;
    }

    if ( $_POST['http_lib'] == 'curl' ) {
        if ( !function_exists('curl_init') ) {
            $options['httpLib'] = 'snoopy';
            $append .= '&curl=1';
        } else {
            $options['httpLib'] = 'curl';
        }
    } else {
        $options['httpLib'] = 'snoopy';
    }
    
    update_option('nowWatchingOptions', $options);

    global $wp_rewrite;
    if ($wp_rewrite->using_mod_rewrite_permalinks() ) {
        nw_mod_rewrite($wp_rewrite->rewrite_rules() );
        $wp_rewrite->flush_rules();
    }

    wp_redirect($nw_url->urls['options'] . "&updated=1$append");
    die;
}

?>
