<?php
/**
 * Aurora Heatmap Plain text Email Template
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

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo $subject_head . PHP_EOL;
echo str_repeat( '=', mb_strwidth( $subject_head ) ) . PHP_EOL;
echo get_bloginfo( 'name' ) . PHP_EOL;
echo get_home_url() . PHP_EOL;
echo PHP_EOL;

// translators: %1$s: from %2$s: to.
printf( __( 'This is a weekly email on %1$s to %2$s.', 'aurora-heatmap' ), $ahm_email->data['the_week']['from'], $ahm_email->data['the_week']['to'] );
echo PHP_EOL;
echo PHP_EOL;

$headline = __( 'Unread Detection', 'aurora-heatmap' );
echo $headline . PHP_EOL;
echo str_repeat( '-', mb_strwidth( $headline ) ) . PHP_EOL;
echo PHP_EOL;

$unread = $ahm_email->data['unread'];
if ( ! isset( $unread['pc'][0] ) && ! isset( $unread['mobile'][0] ) ) {
	echo __( 'No warning-level pages were found in last week\'s breakaway data.', 'aurora-heatmap' ) . PHP_EOL;
	echo __( 'See details', 'aurora-heatmap' ) . ': ' . admin_url( 'options-general.php?page=' . $ahm_email->data['slug'] . '&tab=unread' ) . PHP_EOL;
	echo PHP_EOL;
} else {
	echo __( 'High level warning pages in the last week.', 'aurora-heatmap' ) . PHP_EOL;
	echo __( 'See details', 'aurora-heatmap' ) . ': ' . admin_url( 'options-general.php?page=' . $ahm_email->data['slug'] . '&tab=unread' ) . PHP_EOL;
	echo PHP_EOL;
	foreach ( $unread as $access_type => $items ) {
		if ( ! $items || ! is_array( $items ) ) {
			continue;
		}
		echo '### ';
		switch ( $access_type ) {
			case 'pc':
				echo __( 'PC', 'aurora-heatmap' );
				break;
			case 'mobile':
				echo __( 'Mobile', 'aurora-heatmap' );
				break;
			default:
				echo $access_type;
		}
		echo PHP_EOL;
		foreach ( $items as $item ) {
			printf( '%3d%% %s', 100 * $item['ratio'], $item['title'] );
			echo PHP_EOL;
		}
		echo PHP_EOL;
	}
}

echo PHP_EOL;
echo '--' . PHP_EOL;
echo 'Aurora Heatmap' . PHP_EOL;

/* vim: set ts=4 sw=4 sts=4 noet: */
