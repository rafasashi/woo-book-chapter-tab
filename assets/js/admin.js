;(function($){

	$(document).ready(function(){

		if( $(".wbch-add-input-group").length ){
			
			if( $( ".sortable .ui-sortable" ).length ){
				
				$( ".sortable .ui-sortable" ).sortable({
					
					placeholder	: "ui-state-highlight",
					items		: "li:not(.ui-state-disabled)",
					start: function (e, ui) {
						
						
					},
					stop: function (e, ui) {
						
						
					}
				});
				
				$( ".sortable .ui-sortable li" ).disableSelection();
			}
			
			//input group section add row
			
			function wbch_input_group_section_add_row(e){
					
				e.preventDefault();

				var target = "." + $(this).data("target");
				
				var clone = $(target).eq(0).clone();
				
				clone.css('display','inline-block');

				$('<a class="remove-input-group-section input-group-section-btn" href="#">x</a>').insertAfter(clone.find('input:last'));
				
				$('<a class="move-down-input-group-section input-group-section-btn" href="#">↓</a>').insertAfter(clone.find('input:last'));
				
				$('<a class="move-up-input-group-section input-group-section-btn" href="#">↑</a>').insertAfter(clone.find('input:last'));
				
				$(this).next(".input-group-section").append(clone);
				
			}

			function wbch_input_group_section_remove_row(e){

				e.preventDefault();
				$(this).closest('.input-group-section-row').remove();
			
			}			
			
			function wbch_input_group_section_move_row_up(e){

				e.preventDefault();
				
				$row = $(this).closest('.input-group-section-row');
				
				if( $row.prev(".input-group-section-row").length ){

					$row.prev(".input-group-section-row").before($row.clone());
					
					$row.remove();
				}
			
			}

			function wbch_input_group_section_move_row_down(e){

				e.preventDefault();
				
				$row = $(this).closest('.input-group-section-row');
				
				if( $row.next(".input-group-section-row").length ){
				
					$row.next(".input-group-section-row").after($row.clone());
					
					$row.remove();
				}
			}			
			
			//input group add row

			$(".wbch-add-input-group").on('click', function(e){
				
				e.preventDefault();

				var target = "." + $(this).data("target");
				
				var clone = $(target).eq(0).clone().removeClass('ui-state-disabled');
				
				clone.css('display','inline-block');

				clone.find('textarea').addClass('wbch-rich-text').uniqueId();
				
				//clone.append('<a class="remove-input-group" href="#">remove</a>');

				$('<a class="remove-input-group" href="#">remove</a>').insertAfter(clone.find('input:first'));

				$(this).next(".input-group").append(clone);
				
				$(".wbch-add-input-group-section").on('click', wbch_input_group_section_add_row );
			});
			
			$(".input-group").on('click', ".remove-input-group", function(e){

				e.preventDefault();
				$(this).closest('.input-group-row').remove();
			});
			
			//input group section add row

			$(".wbch-add-input-group-section").on('click', wbch_input_group_section_add_row );
			
			$(".input-group-section")
			.on('click', ".remove-input-group-section", wbch_input_group_section_remove_row )
			.on('click', ".move-up-input-group-section", wbch_input_group_section_move_row_up)
			.on('click', ".move-down-input-group-section", wbch_input_group_section_move_row_down);
		}
	});
		
})(jQuery);