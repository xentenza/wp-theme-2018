<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package epfl
 */


/**
 * Custom nav menu walker for the top bar
 *
 * + Remove so-called "phantom" top-level navigation entries such as "Labs",
 *   that only exist as placeholders
 *
 * + Substitute locally-defined nav menu items with the same set_title.
 *   This lets e.g. school-level sites redirect their "Education", "Research" etc.
 *   pages
 */
class EPFL_Theme2018_Root_Menu_Walker extends Walker_Nav_Menu {
    function start_el (&$output, $item, $depth = 0, $args = array(), $id = 0) {
        if ($surrogate_menu = $this->_get_surrogate_menu()) {
            foreach ($surrogate_menu as $surrogate_item) {
                if ($item->title === $surrogate_item->title) {
                    $item = $surrogate_item;
                    break;
                }
            }
        }
        return parent::start_el($output, $item, $depth, $args, $id);
    }

    private function _get_surrogate_menu () {
        require_once(__DIR__ . '/functions.php');
        if (! root_menu_overrides_enabled()) return false;

        if (! property_exists($this, '_surrogate_menu')) {
            global $EPFL_MENU_OVERRIDE_LOCATION;
            $this->_surrogate_menu = wp_get_nav_menu_items(get_current_menu_slug($EPFL_MENU_OVERRIDE_LOCATION));
        }
        return $this->_surrogate_menu;
    }

    /**
    * Overridden to discard "phantom" root menu entries; that is,
    * entries whose only child is an ExternalMenuItem (as determined
    * by the EPFL plug-in setting a ->epfl_external_menu_children_count
    * annotation for us).
    */
    public function walk( $elements, $max_depth ) {
        $self = $this;
        $filtered_elements = array_filter($elements,
        function($element) use ($self, $elements) {
            return ! $this->_is_phantom_node($elements, $element);
        });
        return parent::walk($filtered_elements, $max_depth);
    }

    function _is_phantom_node ($elements, $element) {
        $self = $this;
        if (property_exists($element, 'epfl_external_menu_children_count') &&
        $element->epfl_external_menu_children_count == 1) {
            $other_children_count = count(array_filter($elements,
            function($child) use ($self, $element) {
                return ($child->menu_item_parent == $element->ID &&
                $child->epfl_soa !== $self->_get_our_soa());
            }));
            if ($other_children_count === 0) return true;
        }
    }

    function _get_our_soa () {
        if (! $this->_our_soa) {
            $this->_our_soa = site_url();
            if (! preg_match('#/$#', $this->_our_soa)) {
                $this->_our_soa .= '/';
            }
        }
        return $this->_our_soa;
    }
}

?>
<?php
	// celebration link builder
	$language = get_current_language();

	if ($language === 'fr') {
		$celebration_url = 'https://www.epfl.ch/campus/events/fr/celebration/';
	} else {
		$celebration_url = 'https://www.epfl.ch/campus/events/celebration-en/';
	}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<script type="text/javascript">window.svgPath = "<?php bloginfo('template_url'); ?>/assets/icons/icons.svg"</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="sr-only" href="#content"><?php esc_html_e( 'Skip to content', 'epfl' ); ?></a>

	<header role="banner" class="header">

  	<div class="site-branding">
  	    <a class="logo" href="<?php echo get_epfl_home_url(); ?>">
  			<img src="<?php bloginfo('template_url'); ?>/assets/svg/epfl-logo.svg" alt="Logo EPFL, École polytechnique fédérale de Lausanne" class="img-fluid">
  		</a>
  		<a class="logo-50" href="<?php echo $celebration_url; ?>">
    		<img src="<?php bloginfo('template_url'); ?>/theme/img/epfl-logo-50-150x111.gif" alt="Logo EPFL 50e anniversaire" width="101" height="91">
  		</a>
  	</div>

	<?php
		global $EPFL_MENU_LOCATION;

		/**
		 * Filters whether the EPFL plugin provides a properly "stitched-up" version of the root (top) menu.
		 *
		 * By default (i.e. EPFL plugin is not installed), or in case of a configuration error, should
		 * return false so that a default root menu is substituted in the top navigation banner.
		 */
		$root_menu_functional_in_plugin = apply_filters('epfl_root_menu_ready', false, $EPFL_MENU_LOCATION);

		if ($root_menu_functional_in_plugin) {
				global $EPFL_MENU_LOCATION;
				wp_nav_menu( array(
					'theme_location' => $EPFL_MENU_LOCATION,
					'menu_id'        => $EPFL_MENU_LOCATION.'-menu',
					'menu_class'=> 'nav-header d-none d-xl-flex',
					'container' => 'ul',
					'depth' => 1,
					'walker' => new EPFL_Theme2018_Root_Menu_Walker()
				) );
			} else {
				require_once(__DIR__ . "/header-top-menu-fallback.php");
			}
	?>

	<div class="dropdown dropright search d-none d-xl-block">
		<a class="dropdown-toggle" href="#" data-toggle="dropdown">
			<svg class="icon" aria-hidden="true"><use xlink:href="#icon-search"></use></svg>
		</a>
		<form action="https://search.epfl.ch/" class="dropdown-menu border-0 p-0">
			<div class="search-form mt-1 input-group">
				<label for="search" class="sr-only">Rechercher sur le site</label>
				<input type="text" class="form-control" name="q" placeholder="Rechercher" >
				<button type="submit" class="d-none d-xl-block btn btn-primary input-group-append" type="button">Valider</button>
			</div>
		</form>
	</div>

	<form action="https://search.epfl.ch/" class="d-xl-none">
		<div class="input-group search-mobile" role="search">
			<div class="input-group-prepend">
				<span class="input-group-text">
					<svg class="icon" aria-hidden="true"><use xlink:href="#icon-search"></use></svg>
				</span>
			</div>
				<label for="search" class="sr-only">Rechercher sur le site</label>
				<input type="text" class="form-control" name="q" placeholder="Rechercher">
		</div>
	</form>

	<?php get_template_part( 'template-parts/language-switcher' ) ?>

  <div class="btn btn-secondary nav-toggle-mobile d-xl-none">
		Menu
		<div class="hamburger">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>

</header>

<div class="main-container">
