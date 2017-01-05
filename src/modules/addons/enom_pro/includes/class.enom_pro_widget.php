<?php

/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@.
 */
class enom_pro_widget
{
    private $base_id;
    private $callback;
	/**
	 * name of the widget as filtered/used by WHMCS 7.1
	 * @var string
	 */
	private $whmcsWidgetName;
	/**
	 * ID of DOM element inside of WHMCS admin homepage
	 * @var string
	 */
	private $whmcsPanelName;

    private $title;
    private $jQuery = '';
    private $action = '';
    private $content_id = '';
    private $icon = '';

    /**
     * enom_pro_widget constructor.
     *
     * @param $title
     * @param $base_id
     * @param $callback
     */
    public function __construct($title, $base_id, $callback)
    {
        if (!class_exists('enom_pro')) {
            require_once 'enom_pro.php';
        }
		$this->whmcsWidgetName = preg_replace('/[^\da-z]/i', '', strtolower($title));
		$this->whmcsPanelName = 'panel' . $this->whmcsWidgetName;
        $this->title = $title;
		$this->callback = $callback;


		//TODO remove these legacy strings
        $this->base_id = 'enom_pro_'.$base_id;
        $this->action = 'refresh_'.$this->base_id;
        $this->content_id = 'content_'.$this->base_id;

    }

    /**
     * Icomoon full class name only.
     */
    public function setIcon($class)
    {
        $this->icon = $class;
    }

    /**
     * Gets true base ID of widget.
     *
     * @return string $base_id
     */
    public function getBaseID()
    {
        return $this->base_id;
    }

    public function getContentID()
    {
        return $this->content_id;
    }

    /**
     * @param string $script
     */
    public function addjQuery($script)
    {
        $this->jQuery = $script;
    }

    public function getContent()
    {
        if (
			isset($_POST['action']) &&
			isset($_POST['widget']) &&
			'refreshwidget' == $_POST['action'] &&
			$this->whmcsWidgetName == $_POST['widget']
		) {
			if (!method_exists($this->callback[0], $this->callback[1])) {
                return ('Unknown callback: '.get_class($this->callback[0]).'::'.$this->callback[1]);
            } else {
                try {
                    ob_start();

                    new enom_pro_license();
                    call_user_func($this->callback);

                    $data = ob_get_contents();
                    ob_end_clean();

                    return $data;
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        } else {
            return '<div id="'.$this->content_id.'" class="enom_pro_output">'.'<span class="enom_pro_loader"></span>'.'</div>';
        }
    }

    /**
     * Gets WHMCS formatted array.
     *
     * @return array
     */
    public function toArray()
    {
        $return = array();
        $iconSpan = '';
        if ($this->icon) {
            $iconSpan = '<span class="enom-pro-icon enom-pro-widget-icon '.$this->icon.'"></span>';
        }
        $return['title'] = $this->title;
        $return['content'] = $this->getContent();
        $return['jquerycode'] = $this->get_jQuery();

        return $return;
    }

    public function getFormID()
    {
        return $this->base_id;
    }

    private function get_jQuery()
    {
        $jQuery = $this->jQuery;
		$id = $this->whmcsPanelName;
        $jSCode = <<<JS
jQuery(function($) {
		var panel = $("#{$id}"),
		    body = panel.find('.panel-body'),
		    refresh = panel.find('.widget-refresh'),
		    toggle = panel.find('.widget-minimise');
		
		toggle.on('click', function() {
			setTimeout(function(){
				panel.trigger('refresh');
			},200)
		});
		
		panel.on('refresh', function() {
			if (toggle.find('i').hasClass('fa-chevron-up') ) {
					refresh.trigger("click");
			}
		}).trigger('refresh');

	{$jQuery}
});
JS;
		if (! isset($_POST['refresh'])) {
        	return enom_pro::minify($jSCode);
		}
    }
}
