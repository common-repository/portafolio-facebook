var $ =jQuery.noConflict();
$(document).ready(function(){			
$(".gallery:first a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'normal',slideshow:3000, social_tools: false, autoplay_slideshow: false});
});
