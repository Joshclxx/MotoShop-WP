<?php
/**
 * Classic Header Template — SwiftCart COD.
 *
 * Outputs the HTML document opening, <head>, and wp_head() for classic
 * templates (front-page.php, archive-product.php, etc.).
 *
 * FSE block templates that don't call get_header() are unaffected.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
