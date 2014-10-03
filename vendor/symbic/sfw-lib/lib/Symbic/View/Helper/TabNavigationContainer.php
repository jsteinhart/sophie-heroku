<?php
class Symbic_View_Helper_TabNavigationContainer extends Zend_View_Helper_Abstract
{
	public function tabNavigationContainer($id, $navTabs, $activeTab, $htmlAttribs = array())
	{
		$class = 'nav nav-tabs';
		if(!empty($htmlAttribs['class']))
		{
			$class .= $htmlAttribs['class'];
			unset($htmlAttribs['class']);
		}
		$script = '
			(function(window, $)
			{
				var module		= {},
					id			= "'.$id.'"
					activeTab	= "'.$activeTab.'" ;

				module.init 	= function()
				{
					//init eventHandler
					$("#" + id + " a").click(function (e) {
						e.preventDefault();
						$(this).tab("show");
					});

					//load currentTab
					$("#" + id + " a[href=\"#" + activeTab + "\"]")
						.trigger("show.bs.tab")
						.tab("show");

					//set eventHandler for ajaxLoading tabs
					$("#" + id + " a[data-toggle=\"tab\"]").on("show.bs.tab", function (e)
					{
						//getTarget
						target = $($(this).attr("href"));
						module.loadTab(target);
					});

					//setup Preloading
					$("div[data-preloadtab=\"true\"]").each( function( index, div)
					{
						module.loadTab($(div));
					});
				};


				module.loadTab = function(target)
				{
					//get url
					url = target.attr("data-href");

					//get custom parameters
					refreshOnShow 	= (target.attr("data-refreshonshow") !== "true") ? false : true;
					preventCache	= target.attr("data-preventcache");

					if(url !== undefined && url !== "")
					{
						//Check if refreshOnShow or data is already loaded
						if (refreshOnShow || target.data("loaded") === undefined)
						{
							cache = (preventCache !== "false") ? true : false;
							$.ajax({
								type: "GET",
								url : url,
								cache: cache,
								beforeSend: function()
								{
									target.html("<div style=\"text-align:center;margin:10px; padding:10px;\"><img src=\"/_media/ajax-loader-big.gif\" title=\"Loading\"></div>");
								}
							}).done(function(data){
								target.html(data);
								//set attribute as loaded
								target.data("loaded", "true");
							});
						}
					}
				};

				module.init();

			}) ( window, window.jQuery);

		';
		$this->view->inlineScript()->appendScript($script);
		$html = '<ul id="'. $id . '" class="' . $class . '"';
		foreach($htmlAttribs as $attrib => $value)
		{
			$html .= ' '. $attrib .'="'.$value.'"';
		}
		$html .= '>' . $navTabs . '</ul>';

		return $html;
	}
}