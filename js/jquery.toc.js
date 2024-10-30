jQuery(document).ready(function($) {
	function hide_toc(e) {
		e.css( "width",  "21px" );
		e.css( "height", "18px" );
		e.find( "ul" ).hide();
		e.find( "span" ).hide();
	}
	function show_toc(e){
		e.css( "width",  "250px" );
		e.css( "height", "auto" );
		e.find('ul').show();
		e.find('span').show();
	}
	function toggle_toc(e){
		(e.find('span:first').is(":hidden"))? show_toc(e) : hide_toc(e) ;
	}

	$( ".toc" ).each(function(){ hide_toc($(this))});;
	$( ".toc" ).click(function(event){
		if (event.target.nodeName!="A") toggle_toc($(this));
		});
});
