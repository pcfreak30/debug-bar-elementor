<?php

use Elementor\Core\Base\Document;
use Elementor\Element_Base;
use Elementor\Plugin;

/**
 * Class Debug_Bar_Elementor
 */
class Debug_Bar_Elementor extends Debug_Bar_Panel {

	/**
	 * @var array
	 */
	private $documents = [];
	/**
	 * @var array
	 */
	private $timing_start = [];
	/**
	 * @var array
	 */
	private $timing_end = [];

	/**
	 * @param \Elementor\Element_Base $element
	 */
	public function start_recording( Element_Base $element ) {
		$this->timing_start[ $element->get_id() ] = microtime( true );
	}

	/**
	 * @param \Elementor\Element_Base $element
	 */
	public function stop_recording( Element_Base $element ) {
		$this->timing_end[ $element->get_id() ] = microtime( true );
	}

	/**
	 *
	 */
	public function init() {
		$this->title( __( 'Elementor', 'debug-bar-elementor' ) );
		add_action( 'elementor/frontend/before_render', [ $this, 'start_recording' ] );
		add_action( 'elementor/frontend/after_render', [ $this, 'stop_recording' ] );
		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'add_document' ], 10, 2 );

	}

	/**
	 * @param $data
	 * @param $post_id
	 *wo
	 *
	 * @return mixed
	 */
	public function add_document( $data, $post_id ) {
		$this->documents[] = $post_id;

		return $data;
	}

	/**
	 *
	 */
	public function render() {
		remove_filter( 'elementor/frontend/builder_content_data', [ $this, 'add_document' ] );
		?>
		<div id="debug-bar-elementor">
			<div class="debug-bar-elementor-accordian">
				<?php
				usort( $this->documents, [ $this, 'sort_documents' ] );
				foreach ( $this->documents as $document ):
					$document = Plugin::$instance->documents->get( $document );
					if ( ! $document ) {
						continue;
					}
					?>
					<h3><?php _e( 'Document:', 'debug-bar-elementor' ); ?>
						&nbsp;<?= $document->get_post()->post_title; ?>
						&nbsp;<?= $this->get_total_document_time( $document ); ?></h3>
					<div>
						<?php $this->render_document( $document ) ?>
					</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param $document
	 *
	 * @return int|mixed
	 */
	private function get_total_document_time( $document ) {
		if ( ! ( $document instanceof Document ) ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$document = Plugin::$instance->documents->get( (int) $document );
			if ( ! $document ) {
				return 0;
			}
		}
		$elements_data = $document->get_elements_data();
		$time          = 0;
		foreach ( $elements_data as $element ) {
			$time += $this->get_element_time( $element['id'] );
		}

		return $time;
	}

	/**
	 * @param $id
	 *
	 * @return int|mixed
	 */
	private function get_element_time( $id ) {
		if ( ! isset( $this->timing_end[ $id ] ) || ! isset( $this->timing_start[ $id ] ) ) {
			return 0;
		}

		return $this->timing_end[ $id ] - $this->timing_start[ $id ];
	}

	/**
	 * @param \Elementor\Core\Base\Document $document
	 */
	private function render_document( Document $document ) {
		Plugin::$instance->documents->switch_to_document( $document );
		$elements_data = $document->get_elements_data();
		usort( $elements_data, [ $this, 'sort_elements' ] );
		?>
		<div class="debug-bar-elementor-accordian">
			<?php foreach ( $elements_data as $element ): ?>
				<?php $this->render_element( $element ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		Plugin::$instance->documents->restore_document();
	}

	/**
	 * @param array $element
	 */
	private function render_element( array $element ) {
		?>
		<h3><?php _e( 'Element:', 'debug-bar-elementor' ); ?>&nbsp;<?= $element['id']; ?>
			&nbsp;<?php _e( 'Type:', 'debug-bar-elementor' ); ?>&nbsp;<?= $this->get_element_type( $element ) ?>
			&nbsp;<?php _e( 'Time:', 'debug-bar-elementor' ); ?>&nbsp;<?= $this->get_element_time( $element['id'] ) ?>
			&nbsp;<a data-id="<?= $element['id']; ?>">Jump to Element</a></h3>
		<?php
		if ( 0 == count( $element['elements'] ) ) {
			return;
		}
		usort( $element['elements'], [ $this, 'sort_elements' ] );
		?>
		<div class="debug-bar-elementor-accordian">
			<?php foreach ( $element['elements'] as $child_element ):
				?>
				<?php $this->render_element( $child_element ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * @param array $element
	 *
	 * @return mixed|string
	 */
	private function get_element_type( array $element ) {
		if ( 'widget' === $element['elType'] ) {
			$widget = Plugin::$instance->widgets_manager->get_widget_types( $element['widgetType'] );
			if ( $widget ) {
				return $widget->get_name();
			}

			return $element['widgetType'];
		}

		$el = Plugin::$instance->elements_manager->get_element_types( $element['elType'] );
		if ( $el ) {
			return $el->get_name();
		}

		return $element['elType'];
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int|mixed
	 */
	private function sort_elements( $a, $b ) {
		return $this->get_element_time( $b['id'] ) - $this->get_element_time( $a['id'] );
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int|mixed
	 */
	private function sort_documents( $a, $b ) {
		return $this->get_total_document_time( $b ) - $this->get_total_document_time( $a );
	}
}
