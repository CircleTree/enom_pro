<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

class enom_pro_widget {
	private $base_id;
	private $callback;
	private $title;
	private $jQuery = '';
	private $action = '';
	private $content_id = '';
	private $icon = '';
	function __construct($title, $base_id, $callback) {
		if (!class_exists('enom_pro')) {
			require_once 'enom_pro.php';
		}
		$this->title = $title;
		$this->base_id = 'enom_pro_' . $base_id;
		$this->action = 'refresh_' . $this->base_id;
		$this->content_id = 'content_'. $this->base_id;
		$this->callback = $callback;
		if (isset($_REQUEST[$this->action])) {
			if (! method_exists($this->callback[0], $this->callback[1])) {
				die('Unknown callback: ' . get_class($this->callback[0]) . '::' . $this->callback[1]);
			} else {
				try {
					call_user_func($this->callback);
				} catch (Exception $e) {
					echo $e->getMessage();
				}
				die;
			}
		}
	}

	/**
	 * Icomoon full class name only
	 */
	public function setIcon ($class)
	{
		$this->icon = $class;
	}

	/**
	 * Gets true base ID of widget
	 * @return string $base_id
	 */
	public function getBaseID ()
	{
		return $this->base_id;
	}
	public function getContentID ()
	{
		return $this->content_id;
	}
	/**
	 * @param string $script
	 */
	public function addjQuery ($script)
	{
		$this->jQuery = $script;
	}
	public function getContent ()
	{
		return '<div id="'. $this->content_id . '"><span class="enom_pro_loader"></span></div>';
	}

	/**
	 * Gets WHMCS formatted array
	 * @return array
	 */
	public function toArray ()
	{
		$return = array();
		$iconSpan = '';
		if ( $this->icon ) {
			$iconSpan = '<span class="enom-pro-icon '.$this->icon.'"></span>';
		}
		$return['title'] = '<a href="'.enom_pro::MODULE_LINK.'">'.ENOM_PRO.'</a> ' . $this->title . $iconSpan . $this->getWidgetForm();
		$return['content'] = $this->getContent();
		$return['jquerycode'] = $this->get_jQuery() . $this->jQuery;
		return $return;
	}
	public function getFormID ()
	{
		return $this->base_id;
	}
	private function getWidgetForm ()
	{
		if ('configadminroles.php' == basename($_SERVER['PHP_SELF'])) {
			return '';
		}
		ob_start();?>
		<form id="<?php echo $this->base_id; ?>" class="refreshbutton" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<input type="hidden" name="<?php echo $this->action; ?>" value="1" />
			<button type="submit" class="btn btn-default btn-xs">
				Refresh <span class="enom-pro-icon enom-pro-icon-refresh-alt fa-spin"></span>
			</button>
		</form>
		<?php
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}
	private function get_jQuery ()
	{
		ob_start();
		?>
		var $refreshForm = jQuery("#<?php echo $this->base_id; ?>"),
		$refreshButton = $refreshForm.find('.enom-pro-icon-refresh-alt');
		$refreshForm.on("submit", function() {
		var $content = jQuery("#<?php echo $this->content_id; ?>");
		$content.html('<span class="enom_pro_loader"></span>');
		$refreshButton.addClass('fa-spin');
		jQuery.post("index.php", $(this).serialize(), function(data) {
		$refreshButton.removeClass('fa-spin');
		$content.html(data);
		});
		return false;
		});
		if ($refreshForm.is(":visible")) {
		$refreshForm.trigger("submit");
		}
		<?php
		$jquery = ob_get_contents();
		ob_end_clean();
		return enom_pro::minify($jquery);
	}

}