<?php
/*
Plugin Name: Alphabetize Admin
Plugin URI: https://wpboosted.com
Description: A plugin that allows users to Alphabetize the admin menu.
Version: 1.0
Author: Mayur Jobanputra
Author URI: https://wpboosted.com
*/

// Toggle the admin menu sorting
function toggle_admin_menu_sort() {
    $is_enabled = get_option('admin_menu_sort_enabled', false);
    update_option('admin_menu_sort_enabled', !$is_enabled);
    wp_safe_redirect($_GET['redirect']);
    exit;
}

// Generate the button for the "Alphabetize Admin" page
function admin_menu_sort_button() {
    $is_enabled = get_option('admin_menu_sort_enabled', false);
    $text = $is_enabled ? 'Turn Off' : 'Turn On';
    $url = admin_url('admin-post.php?action=toggle_admin_menu_sort&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    $button = sprintf('<a href="%s" class="button">%s</a>', $url, $text);
    echo $button;
}

// Add JavaScript to sort the admin menu items
function sort_admin_menu_javascript() {
    $is_enabled = get_option('admin_menu_sort_enabled', false);
    if ($is_enabled) {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var $adminMenu = $('#adminmenu');
                var $loading = $('<div class="loading-overlay"></div>');
                $adminMenu.before($loading);
                setTimeout(function() {
                    $loading.remove();
                    var menu = $('#adminmenu li a');
                    menu.sort(function(a, b) {
                        var aText = a.text.toLowerCase();
                        var bText = b.text.toLowerCase();
                        if (aText === 'dashboard') {
                            return -1;
                        } else if (bText === 'dashboard') {
                            return 1;
                        }
                        return (aText < bText) ? -1 : (aText > bText) ? 1 : 0;
                    });
                    $.each(menu, function(index, li) {
                        li.parentNode.parentNode.appendChild(li.parentNode);
                        $(li).find('.wp-menu-image').remove();
                    });
                }, 100);
            });
        </script>
		
		<!-- displays dashboard box -->
		<script type="text/javascript">
    jQuery(document).ready(function($) {
        if (window.location.href.indexOf("index.php") !== -1) {
            var $dashboardWidgetsWrap = $('#dashboard-widgets-wrap');
            var $quickNavAdmin = $('<div class="quick-nav-admin" style="background-color: black;"></div>');
            $dashboardWidgetsWrap.before($quickNavAdmin);

            var menu = $('#adminmenu > li > a');
            var menuItems = [];

            $.each(menu, function(index, li) {
                if ($(li).parent().hasClass('wp-menu-separator') || $(li).hasClass('wp-first-item')) {
                    return;
                }
                var submenu = $(li).next('ul').html();
                menuItems.push({
                    text: $(li).text(),
                    url: $(li).attr('href'),
                    submenu: submenu
                });
            });

            menuItems.sort(function(a, b) {
                var aText = a.text.toUpperCase();
                var bText = b.text.toUpperCase();
                return (aText < bText) ? -1 : (aText > bText) ? 1 : 0;
            });

            var menuItemChunks = chunk(menuItems, Math.ceil(menuItems.length / 3));
            var $menuList = $('<ul style="display: flex;"></ul>');

            $.each(menuItemChunks, function(index, chunk) {
                var $chunkList = $('<ul style="flex: 1;"></ul>');
                $.each(chunk, function(index, item) {
                    var $item = $('<li class="has-submenu"><a href="' + item.url + '">' + item.text + '</a></li>');
                    if (item.submenu) {
                        var $submenu = $('<div class="submenu-container"><div class="submenu">' + item.submenu + '</div></div>');
                        $item.append($submenu);
                    }
                    $chunkList.append($item);
                });
                $menuList.append($chunkList);
            });

            $quickNavAdmin.append($menuList);
            $quickNavAdmin.on('mouseenter', '.has-submenu', function() {
                $(this).addClass('submenu-open');
            }).on('mouseleave', '.has-submenu', function() {
                $(this).removeClass('submenu-open');
            });
        }
    });

    function chunk(array, size) {
        var chunked = [];
        for (var i = 0; i < array.length; i += size) {
            chunked.push(array.slice(i, i + size));
        }
        return chunked;
    }
</script>

<style>
    #adminmenu div.wp-menu-image {
        display: none;
    }
    #adminmenu div.wp-menu-name,
    #adminmenu li ul li a, #adminmenu .wp-submenu a {
        text-transform: uppercase;
        font-size: 80%;
		color:white;
		padding:8px;
    }
    #adminmenu li.wp-menu-separator {
        display:none;
    }
    #adminmenu li.menu-top {
        min-height: unset;
    }
    #collapse-menu {
        display:none;
    }
    .submenu {
        display: none;
        position: absolute;
        background-color: #333;
        color: #fff;
        min-width: 200px;
        top: 0;
        left: 100%;
        padding: 10px;
        border-radius: 3px;
    }
    .submenu-container {
        position: relative;
        display: inline-block;
    }
    .has-submenu:hover .submenu {
        display: block;
    }
    .submenu-open .submenu {
        display: block;
    }
	.quick-nav-admin {
		display:none;
	}
	.quick-nav-admin li a,.quick-nav-admin li ul li a  {
		color:white;
		text-decoration:none;
		text-transform:uppercase;
		font-size:80%;
	}
	.wp-submenu-head {
		font-size:100%;
		color:yellow;
	}
	.quick-nav-admin .submenu {
		z-index:1;
	}
</style>

    <?php
    }
}


// Add the "Alphabetize Admin" page to the WordPress admin menu
function add_alphabetize_admin_page() {
    add_submenu_page(
        'tools.php',
        'Alphabetize Admin',
        'Alphabetize Admin',
        'manage_options',
        'alphabetize-admin',
        'alphabetize_admin_page'
    );
}

// Generate the "Alphabetize Admin" page
function alphabetize_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Alphabetize Admin</h1>';
	echo '<br clear="all">';

    admin_menu_sort_button();

    echo '</div>';
}


// Register the plugin's actions and filters
add_action('admin_menu', 'add_alphabetize_admin_page');
add_action('admin_post_toggle_admin_menu_sort', 'toggle_admin_menu_sort');
add_action('admin_footer', 'sort_admin_menu_javascript');
