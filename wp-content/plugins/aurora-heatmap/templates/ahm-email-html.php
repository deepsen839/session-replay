<?php
/**
 * Aurora Heatmap HTML Email Template
 *
 * To customize, copy this file to your theme directory and edit.
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subject_head       = __( 'Aurora Heatmap', 'aurora-heatmap' ) . ' ' . __( 'Weekly Email', 'aurora-heatmap' );
$ahm_email->subject = $subject_head . ' - ' . get_bloginfo( 'name' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html( $ahm_email->subject ); ?></title>
	<style type="text/css">
html {
	color: #000;
	background: #fff;
}
h1 {
	color: #fff;
	background: #086d92;
	text-align: center;
	padding: .25em 0;
}
h1 a {
	color: #eef;
}
h2 {
	color: #086d92;
	text-align: center;
}
table.layout {
	border: none;
}
table.layout tr {
	vertical-align: top;
}
.ratio-text {
	width: 6em;
	text-align: center;
	color: #c00;
	font-weight: bold;
}
.ratio-bar-container {
	width: 6em;
	height: 2px;
	background: #eee;
}
.ratio-bar {
	height: 2px;
	background: #cdad00;
}
hr {
	border-style: solid;
	opacity: 0.5;
}
.footer {
	font-weight: bold;
	text-align: right;
	color: #aaa;
	text-shadow: 1px 1px 1px #eee;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}
	</style>
</head>
<body>
	<h1><?php echo esc_html( $subject_head ) . '<br><a href="' . esc_url( get_home_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'; ?></h1>
	<p>
		<?php
		echo esc_html(
			sprintf(
				// translators: %1$s: from %2$s: to.
				__( 'This is a weekly email on %1$s to %2$s.', 'aurora-heatmap' ),
				$ahm_email->data['the_week']['from'],
				$ahm_email->data['the_week']['to']
			)
		);
		?>
	</p>
	<hr>
	<h2><?php esc_html_e( 'Unread Detection', 'aurora-heatmap' ); ?></h2>
	<?php $unread = $ahm_email->data['unread']; ?>
	<?php if ( ! isset( $unread['pc'][0] ) && ! isset( $unread['mobile'][0] ) ) : ?>
		<p>
			<?php esc_html_e( 'No warning-level pages were found in last week\'s breakaway data.', 'aurora-heatmap' ); ?>
			<small>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $ahm_email->data['slug'] . '&tab=unread' ) ); ?>">
					<?php esc_html_e( 'See details', 'aurora-heatmap' ); ?>
				</a>
			</small>
		</p>
	<?php else : ?>
		<p>
			<?php esc_html_e( 'High level warning pages in the last week.', 'aurora-heatmap' ); ?>
			<small>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $ahm_email->data['slug'] . '&tab=unread' ) ); ?>">
					<?php esc_html_e( 'See details', 'aurora-heatmap' ); ?>
				</a>
			</small>
		</p>
		<?php foreach ( $unread as $access_type => $items ) : ?>
			<?php
			if ( ! $items || ! is_array( $items ) ) {
				continue;
			}
			?>
			<h3>
			<?php
			switch ( $access_type ) {
				case 'pc':
					esc_html_e( 'PC', 'aurora-heatmap' );
					break;
				case 'mobile':
					esc_html_e( 'Mobile', 'aurora-heatmap' );
					break;
				default:
					echo esc_html( $access_type );
			}
			?>
			</h3>
			<table class="layout">
				<?php foreach ( $items as $item ) : ?>
					<tr>
						<td>
							<div class="ratio-text"><?php echo esc_html( intval( 100 * $item['ratio'] ) ); ?> %</div>
							<div class="ratio-bar-container"><div class="ratio-bar" style="width: <?php echo esc_attr( round( 100 * $item['ratio'], 2 ) ); ?>%"></div></div>
						</td>
						<td><?php echo esc_html( $item['title'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endforeach; ?>
	<?php endif; ?>
	<hr>
	<div class="footer">Aurora Heatmap</div>
</body>
</html>
<?php

/* vim: set ts=4 sw=4 sts=4 noet: */
