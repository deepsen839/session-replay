<?php
/**
 * Aurora Heatmap List Class
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Aurora_Heatmap_List
 */
class Aurora_Heatmap_List extends WP_List_Table {

	/**
	 * Aurora_Heatmap object
	 *
	 * @var object
	 */
	private $heatmap;

	/**
	 * Constructor
	 *
	 * @param object $heatmap Aurora_Heatmap object.
	 */
	public function __construct( $heatmap ) {
		parent::__construct( array( 'ajax' => false ) );
		$this->heatmap = $heatmap;
	}

	/**
	 * Get table classes
	 *
	 * Remove fixed class.
	 */
	public function get_table_classes() {
		return array_diff( parent::get_table_classes(), array( 'fixed' ) );
	}

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$pc        = '<i class="dashicons dashicons-laptop"></i>';
		$mobile    = '<i class="dashicons dashicons-smartphone"></i>';
		$click     = '<i class="dashicons dashicons-location"></i>';
		$breakaway = '<i class="dashicons dashicons-migrate"></i>';
		$attention = '<i class="dashicons dashicons-visibility"></i>';
		return array(
			'cb'               => '<input type="checkbox" />',
			'page'             => _x( 'Page', 'List_Table', 'aurora-heatmap' ),
			'click_pc'         => $pc . $click,
			'breakaway_pc'     => $pc . $breakaway,
			'attention_pc'     => $pc . $attention,
			'click_mobile'     => $mobile . $click,
			'breakaway_mobile' => $mobile . $breakaway,
			'attention_mobile' => $mobile . $attention,
		);
	}

	/**
	 * Get sortable columns
	 */
	public function get_sortable_columns() {
		$orderby = filter_input( INPUT_GET, 'orderby' );
		if ( ! $orderby ) {
			$orderby = 'click_pc';
		}

		$sortable = array(
			'page'             => array( 'page', false ),
			'click_pc'         => array( 'click_pc', ( 'click_pc' !== $orderby ) ),
			'breakaway_pc'     => array( 'breakaway_pc', true ),
			'attention_pc'     => array( 'attention_pc', true ),
			'click_mobile'     => array( 'click_mobile', true ),
			'breakaway_mobile' => array( 'breakaway_mobile', true ),
			'attention_mobile' => array( 'attention_mobile', true ),
		);

		$heatmap = $this->heatmap;
		if ( 'basic' === $heatmap::PLAN || 'free' === $heatmap::PLAN ) {
			unset( $sortable['breakaway_pc'] );
			unset( $sortable['attention_pc'] );
			unset( $sortable['breakaway_mobile'] );
			unset( $sortable['attention_mobile'] );
		}

		return $sortable;
	}

	/**
	 * Print column headers
	 *
	 * @param bool $with_id with_id.
	 */
	public function print_column_headers( $with_id = true ) {
		ob_start();
		parent::print_column_headers( $with_id );
		$titles = array(
			_x( 'Page', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Click', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Breakaway', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Attention', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Click', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Breakaway', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Attention', 'List_Table', 'aurora-heatmap' ),
		);
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo preg_replace_callback(
			'/<th\\s+/',
			function ( $matches ) use ( $titles ) {
				static $index = -1;
				if ( isset( $titles[ ++$index ] ) ) {
					return '<th title="' . esc_attr( $titles[ $index ] ) . '" ';
				} else {
					return $matches[0];
				}
			},
			ob_get_clean()
		);
	}

	/**
	 * Single row columns
	 *
	 * @param object $item The current item.
	 */
	public function single_row_columns( $item ) {
		ob_start();
		parent::single_row_columns( $item );
		$colnames = array(
			_x( 'Page', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Click', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Breakaway', 'List_Table', 'aurora-heatmap' ),
			_x( 'PC Attention', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Click', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Breakaway', 'List_Table', 'aurora-heatmap' ),
			_x( 'Mobile Attention', 'List_Table', 'aurora-heatmap' ),
		);
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo preg_replace_callback(
			'/<td\\s+([^>]+)\\s+data-colname=[\'"][^\'"]*[\'"]/',
			function ( $matches ) use ( $colnames ) {
				static $index = -1;
				if ( isset( $colnames[ ++$index ] ) ) {
					return '<td data-colname="' . esc_attr( $colnames[ $index ] ) . '" ' . $matches[1];
				} else {
					return $matches[0];
				}
			},
			ob_get_clean()
		);
	}

	/**
	 * Get bulk actions
	 *
	 * Add delete action.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => _x( 'Delete', 'List_Table', 'aurora-heatmap' ),
		);
	}

	/**
	 * Prepare items
	 */
	public function prepare_items() {
		global $wpdb;
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$orderby = filter_input( INPUT_GET, 'orderby' );
		if ( ! $orderby ) {
			$orderby = 'click_pc';
		}

		$test  = filter_input( INPUT_GET, 'order' );
		$test  = $test ? $test : '';
		$order = strtolower( $test );
		if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
			$order = 'click_pc' === $orderby ? 'desc' : 'asc';
		}

		$param = array(
			'search'  => filter_input( INPUT_GET, 's' ),
			'pagenum' => $this->get_pagenum(),
			'orderby' => $orderby,
			'order'   => $order,
		);

		if ( $param['search'] ) {
			$r            = preg_split( '/\s+/', $param['search'] );
			$search_title = array();
			$search_url   = array();
			foreach ( $r as $word ) {
				$search_title[] = $wpdb->esc_like( $word );
				$search_url[]   = $wpdb->esc_like(
					preg_replace_callback(
						'/[^\x21-\x7E]+/',
						function ( $matches ) {
							return rawurlencode( $matches[0] );
						},
						$word
					)
				);
			}
			$param['search_title'] = '%' . implode( '%', $search_title ) . '%';
			$param['search_url']   = '%' . implode( '%', $search_url ) . '%';
		}

		$heatmap          = $this->heatmap;
		$total_items      = $heatmap->get_list_total_items( $param );
		$total_pages      = ceil( $total_items / $heatmap::LIST_PER_PAGE );
		$param['pagenum'] = min( $param['pagenum'], $total_pages );
		$this->items      = $heatmap->get_list_items( $param );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $heatmap::LIST_PER_PAGE,
				'total_pages' => $total_pages,
			)
		);
	}

	/**
	 * Column default
	 *
	 * @param stdClass $item        Item.
	 * @param string   $column_name Column name.
	 */
	public function column_default( $item, $column_name ) {
		$heatmap = $this->heatmap;
		$events  = $heatmap::EVENT_NAMES;

		if ( ! $item->{$column_name} || ! array_key_exists( $column_name, $events ) ) {
			return '<span class="ahm-cell-blank">&mdash;</span>';
		}

		if ( ! $item->url ) {
			return '<span class="ahm-cell-blank">' . esc_html( number_format( $item->{$column_name} ) ) . '</span>';
		}

		$url         = $heatmap->get_heatmap_url( $item, $column_name );
		$access_from = $events[ $column_name ] & 1;
		$access_name = explode( '_', $column_name )[1];
		$view_width  = apply_filters( 'aurora_heatmap_width_' . $access_name . '_default', $heatmap::VIEW_WIDTH[ $access_from ] );

		return '<a class="ahm-cell ahm-view" href="' . esc_attr( $url ) . '" data-url="' . esc_attr( $url ) . '" data-width="' . esc_attr( $view_width ) . '"><span class="count">' . esc_html( number_format( $item->{$column_name} ) ) . '</span> <span class="dashicons dashicons-external"></span></a>';
	}

	/**
	 * Column checkbox
	 *
	 * @param stdClass $item Item.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'id',
			$item->id
		);
	}

	/**
	 * Column Page
	 *
	 * @param stdClass $item Item.
	 */
	public function column_page( $item ) {
		if ( $item->title ) {
			$title = '<strong>' . esc_html( $item->title ) . '</strong>';
		} else {
			$title = '<em>' . __( 'no title', 'aurora-heatmap' ) . '</em>';
		}

		if ( $item->url ) {
			$link = '<a href="' . esc_attr( $item->url ) . '" target="_blank">' . esc_html( urldecode( $item->url ) ) . '</a>';
		} else {
			$link = __( 'sorry, unknown URL', 'aurora-heatmap' );
		}

		return $title . '<br>' . $link;
	}
}

/* vim: set ts=4 sw=4 sts=4 noet: */
