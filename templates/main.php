<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/4/18
 * Time: 5:44 AM
 */
// You can use any JavaScript framework but for this tutorial we want to keep it as simple as possible and therefore only include the templating library handlebarsjs. Download the file into ownnotes/js/handlebars-v4.0.11.js and include it at the very top of ownnotes/templates/main.php before the other scripts and styles.
/**
 * The script method’s first parameter specifies the application which the JavaScript should be included for. This helps increase performance by not including the JavaScript unnecessarily. The script’s second parameter is the name of the JavaScript file, located in the application’s js directory, minus the .js extension. In the case above, ownnotes/js/handlebars-v4.0.11.js would be loaded.
 */
script('ownnotes', 'handlebars-v4.0.11');

// jQuery is included by default on every page.

/**
 * To include CSS, use the template’s style method, as in the example below. As with script, the first parameter is the application to find the CSS file in and the second parameter is the name of the CSS file, minus the .css file extension
 */
style('ownnotes', 'style');  // adds ownnotes/css/style.css

?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>

</div>

<?php
// place custom js logic at the end
script('ownnotes', 'script');
?>