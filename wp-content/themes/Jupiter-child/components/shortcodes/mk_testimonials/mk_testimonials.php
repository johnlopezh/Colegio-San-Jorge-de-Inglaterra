<?php

$path = pathinfo(__FILE__) ['dirname'];
include ($path . '/config.php');

$id = uniqid();

$query = mk_wp_query(array(
    'post_type' => 'testimonial',
    'count' => $count,
    'posts' => $testimonials,
    'categories' => $categories,
    'orderby' => $orderby,
    'order' => $order,
));

$loop = $query['wp_query'];

$atts = array(
    'style' => $style,
    'loop' => $loop,
    'skin' => $skin,
    'column' => $column,
    'el_class' => $el_class,
    'id' => $id,
    'animation_speed' => $animation_speed,
    'slideshow_speed' => $slideshow_speed,
    'animation_css' => get_viewport_animation_class($animation)
);

echo mk_get_shortcode_view('mk_testimonials', 'components/heading-title', true, ['title' => $title, 'skin' => $skin, 'style' => $style]);
echo mk_get_shortcode_view('mk_testimonials', 'show-as/' . $show_as, true, $atts);
