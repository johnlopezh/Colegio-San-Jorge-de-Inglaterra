<?php 

/*
    Nombre: Archvivo Functions.php theme Jupiter Child
    Auto: John Alberto López
    Fecha: 23  de Junio de 2016

*/

    add_action( 'widgets_init', 'theme_slug_widgets_init' );
    function theme_slug_widgets_init() 
    {
        register_sidebar( array(
            'name' => __( 'Seleccion de Idioma' ),
            'id' => 'sidebar-Idioma-1-1',
            'description' => __( 'Sidebar para inlcuir Banderas de selección de Idiomas', 'theme-slug' ),
            'class' => 'sidebar', 
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
          ) );
        register_sidebar( array(
            'name' => __( 'Menu Admisiones' ),
            'description' => __( 'Siderbar para incluir el menu de la sección', 'theme-slug' ),
            'id' => 'sidebar-MenuAdmisiones-2',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
          ) );
        register_sidebar( array(
            'name' => __( 'Menu Tradición', 'theme-slug' ),
            'id' => 'sidebar-MenuTradicion-3',
            'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
          ) );
    }
    

?>
