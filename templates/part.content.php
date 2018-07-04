<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 3:57 AM
 */
/**
 * As noted in the controllers section of the tutorial, templates are, effectively, not much more than the original PHP files, which were a combination of PHP and HTML. However, they can also contain conditional logic, as you can see in the example below.

This template, in ownnotes/templates/part.content.php,contains the core form elements for creating notes.
 *
 * $l->t() is used to make your strings translatable and p() is used to print escaped HTML
 *
 */
?>

<script id="content-tpl" type="text/x-handlebars-template">
	{{#if note}} <!-- this is a block helper. block helpers are basically functions in handlebar.js. Other kinds of functions includes handlebar helpers and handlebar partials. To register your functions call Handlebars.registerHelper() or Handlebars.registerPartial(). For more details, see: https://www.youtube.com/watch?v=4HuAnM6b2d8 -->
	<div class="input"><textarea title="note_content">{{ note.content }}</textarea></div>
	<div class="save"><button><?php p($l->t('Save')); ?></button></div>
	{{else}}
	<div class="input"><textarea disabled title="non_note_content"></textarea></div>
	<div class="save"><button disabled><?php p($l->t('Save')); ?></button></div>
	{{/if}}
</script>
<div id="editor"></div>
