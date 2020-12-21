<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'jcr_file_custom';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2.0';
$plugin['author'] = 'jcr / txpbuilders';
$plugin['author_uri'] = 'http://txp.builders';
$plugin['description'] = 'Adds multiple custom field to the files panel';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '1';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '3';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

// Customise the display of the custom field form labels by pasting the following into the Textpack box
// in Settings › Languages replacing the language code and field label names:
// #@owner jcr_file_custom
// #@language en, en-gb, en-us
// #@file_edit
// jcr_file_custom_1 => Image ID
// jcr_file_custom_2 => Issue year
// jcr_file_custom_3 => Publisher
// jcr_file_custom_5 => ISBN
// jcr_file_custom_4 => Preview Article ID


$plugin['textpack'] = <<<EOT
#@owner jcr_file_custom
#@language en, en-gb, en-us
#@prefs
jcr_file_custom => File custom fields
file_custom_1_set => File custom field 1 name
file_custom_2_set => File custom field 2 name
file_custom_3_set => File custom field 3 name
file_custom_4_set => File custom field 4 name
file_custom_5_set => File custom field 5 name
#@language de
#@prefs
jcr_file_custom => Datei Custom-Felder
file_custom_1_set => Name des 1. Datei-Custom Feldes
file_custom_2_set => Name des 2. Datei-Custom Feldes
file_custom_3_set => Name des 3. Datei-Custom Feldes
file_custom_4_set => Name des 4. Datei-Custom Feldes
file_custom_5_set => Name des 5. Datei-Custom Feldes
EOT;

// End of textpack

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
class jcr_file_custom
{
    /**
     * Initialise.
     */
     function __construct()
    {
    	// Hook into the system's callbacks.
    	register_callback(array(__CLASS__, 'lifecycle'), 'plugin_lifecycle.jcr_file_custom');
    	register_callback(array(__CLASS__, 'ui'), 'file_ui', 'extend_detail_form');
    	register_callback(array(__CLASS__, 'save'), 'file', 'file_save');

        // Prefs pane for custom fields
        add_privs("prefs.jcr_file_custom", "1");

        // Redirect 'Options' link on plugins panel to preferences pane
        add_privs("plugin_prefs.jcr_file_custom", "1");
        register_callback([__CLASS__, "options_prefs_redirect"], "plugin_prefs.jcr_file_custom");
    }

	/**
     * Add and remove custom field from txp_file table.
     *
     * @param $event string
     * @param $step string  The lifecycle phase of this plugin
     */
    public static function lifecycle($event, $step)
    {
        switch ($step) {
            case 'enabled':
                add_privs("prefs.jcr_file_custom", "1");
                break;
            case 'disabled':
                break;
            case 'installed':
                // Add file custom fields to txp_file table
                $cols_exist = safe_query("SHOW COLUMNS FROM " . safe_pfx("txp_file") . " LIKE 'jcr_file_custom_1'");
                if (@numRows($cols_exist) == 0) {
                    safe_alter(
                        "txp_file",
                        "ADD COLUMN jcr_file_custom_1 VARCHAR(255) NOT NULL DEFAULT '' AFTER author,
                         ADD COLUMN jcr_file_custom_2 VARCHAR(255) NOT NULL DEFAULT '' AFTER jcr_file_custom_1,
                         ADD COLUMN jcr_file_custom_3 VARCHAR(255) NOT NULL DEFAULT '' AFTER jcr_file_custom_2,
                         ADD COLUMN jcr_file_custom_4 VARCHAR(255) NOT NULL DEFAULT '' AFTER jcr_file_custom_3,
                         ADD COLUMN jcr_file_custom_5 VARCHAR(255) NOT NULL DEFAULT '' AFTER jcr_file_custom_4"
                    );
                }

                // Add prefs for file custom field names
                create_pref("file_custom_1_set", "", "jcr_file_custom", "0", "file_custom_set", "1");
                create_pref("file_custom_2_set", "", "jcr_file_custom", "0", "file_custom_set", "2");
                create_pref("file_custom_3_set", "", "jcr_file_custom", "0", "file_custom_set", "3");
                create_pref("file_custom_4_set", "", "jcr_file_custom", "0", "file_custom_set", "4");
                create_pref("file_custom_5_set", "", "jcr_file_custom", "0", "file_custom_set", "5");

                // Insert initial value for cf1 if none already exists (so that upgrade works)
                $cf_pref = get_pref("file_custom_1_set");
                if ($cf_pref === "") {
                    set_pref("file_custom_1_set", "custom1");
                }

                // Upgrade: Migrate v1 plugin legacy column
                $legacy = safe_query("SHOW COLUMNS FROM " . safe_pfx("txp_file") . " LIKE 'jcr_file_custom'");
                if (@numRows($legacy) > 0) {
                    // Copy contents of jcr_file_custom to jcr_file_custom_1 (where not empty/NULL)
                    safe_update("txp_file", "`jcr_file_custom_1` = `jcr_file_custom`", "jcr_file_custom IS NOT NULL");
                    // Delete jcr_file_custom column
                    safe_alter("txp_file", "DROP COLUMN `jcr_file_custom`");
                    // Update language string (is seemingly not replaced by textpack)
                    safe_update("txp_lang", "data = 'File custom fields', owner = 'jcr_file_custom'", "name = 'jcr_file_custom' AND lang = 'en'");
                    safe_update("txp_lang", "data = 'Datei Custom-Felder', owner = 'jcr_file_custom'", "name = 'jcr_file_custom' AND lang = 'de'");
                }
                break;
            case 'deleted':
                // Remove columns from file table
                safe_alter(
                    "txp_file",
                    'DROP COLUMN jcr_file_custom_1,
                     DROP COLUMN jcr_file_custom_2,
                     DROP COLUMN jcr_file_custom_3,
                     DROP COLUMN jcr_file_custom_4,
                     DROP COLUMN jcr_file_custom_5'
                );
                // Remove all prefs from event 'jcr_file_custom'.
                remove_pref(null, "jcr_file_custom");

                // Remove all associated lang strings
                safe_delete(
                  "txp_lang",
                  "owner = 'jcr_file_custom'"
                );
                break;
        }
        return;
    }

	/**
	 * Paint additional fields for file custom field
	 *
	 * @param $event string
	 * @param $step string
	 * @param $dummy string
	 * @param $rs array The current file's data
	 * @return string
	 */
	public static function ui($event, $step, $dummy, $rs)
	{
		global $prefs;

        extract(lAtts(array(
			"jcr_file_custom_1" => "",
            "jcr_file_custom_2" => "",
            "jcr_file_custom_3" => "",
            "jcr_file_custom_4" => "",
            "jcr_file_custom_5" => "",
		), $rs, 0));

		$out = "";

        $cfs = preg_grep("/^file_custom_\d+_set/", array_keys($prefs));
        asort($cfs);

        foreach ($cfs as $name) {
            preg_match("/(\d+)/", $name, $match);

            if ($prefs[$name] !== "") {
                $out .= inputLabel("jcr_file_custom_" . $match[1], fInput("text", "jcr_file_custom_" . $match[1], ${"jcr_file_custom_" . $match[1]}, "", "", "", INPUT_REGULAR, "", "jcr_file_custom_" . $match[1]), "jcr_file_custom_" . $match[1]) . n;
            }
        }

        return $out;
	}

	/**
	 * Save additional file custom fields
	 *
	 * @param $event string
	 * @param $step string
	 */
	public static function save($event, $step)
	{
		extract(doSlash(psa(["jcr_file_custom_1", "jcr_file_custom_2", "jcr_file_custom_3", "jcr_file_custom_4", "jcr_file_custom_5", "id"])));
        $id = assert_int($id);
        safe_update(
            "txp_file",
            "jcr_file_custom_1 = '$jcr_file_custom_1',
             jcr_file_custom_2 = '$jcr_file_custom_2',
             jcr_file_custom_3 = '$jcr_file_custom_3',
             jcr_file_custom_4 = '$jcr_file_custom_4',
             jcr_file_custom_5 = '$jcr_file_custom_5'",
            "id = $id"
        );
	}

    /**
     * Renders a HTML file custom field in the prefs.
     *
     * Can be altered by plugins via the 'prefs_ui > file_custom_set'
     * pluggable UI callback event.
     *
     * @param  string $name HTML name of the widget
     * @param  string $val  Initial (or current) content
     * @return string HTML
     * @todo   deprecate or move this when CFs are migrated to the meta store
     */
    public static function file_custom_set($name, $val)
    {
        return pluggable_ui("prefs_ui", "file_custom_set", text_input($name, $val, INPUT_REGULAR), $name, $val);
    }

    /**
     * Re-route 'Options' link on Plugins panel to Admin › Preferences panel
     *
     */
    public static function options_prefs_redirect()
    {
        header("Location: index.php?event=prefs#prefs_group_jcr_file_custom");
    }
}

if (txpinterface === 'admin') {

    new jcr_file_custom();

} elseif (txpinterface === 'public') {

    if (class_exists('\Textpattern\Tag\Registry')) {
        Txp::get('\Textpattern\Tag\Registry')
            ->register('jcr_file_custom')
            ->register("jcr_if_file_custom");
    }

}

/**
 * Gets a list of file custom fields.
 *
 * @return  array
 */
function jcr_get_file_custom_fields()
{
    global $prefs;
    static $out = null;
    // Have cache?
    if (!is_array($out)) {
        $cfs = preg_grep("/^file_custom_\d+_set/", array_keys($prefs));
        $out = [];
        foreach ($cfs as $name) {
            preg_match("/(\d+)/", $name, $match);
            if ($prefs[$name] !== "") {
                $out[$match[1]] = strtolower($prefs[$name]);
            }
        }
    }
    return $out;
}

/**
 * Maps 'txp_file' table's columns to article data values.
 *
 * This function returns an array of 'data-value' => 'column' pairs.
 *
 * @return array
 */
function jcr_file_column_map()
{
    $file_custom = jcr_get_file_custom_fields();
    $file_custom_map = [];

    if ($file_custom) {
        foreach ($file_custom as $i => $name) {
            $file_custom_map[$name] = "jcr_file_custom_" . $i;
        }
    }

    return $file_custom_map;
}

/**
 * Public tag: Output file custom field
 * @param  string $atts[name] Name of custom field.
 * @param  string $atts[escape] Convert special characters to HTML entities.
 * @param  string $atts[default] Default output if field is empty.
 * @return string custom field output
 * <code>
 *        <txp:jcr_file_custom name="title_file" escape="html" />
 * </code>
 */
function jcr_file_custom($atts, $thing = null)
{
    global $thisfile;

    assert_file();

    extract(
        lAtts(
            [
                "class" => "",
                "name" => get_pref("file_custom_1_set"),
                "escape" => null,
                "default" => "",
                "wraptag" => "",
            ],
            $atts
        )
    );

    $name = strtolower($name);

    // Populate file custom field data;
    foreach (jcr_file_column_map() as $key => $column) {
        $thisfile[$key] = isset($column) ? $column : null;
    }

    if (!isset($thisfile[$name])) {
        trigger_error(gTxt("field_not_found", ["{name}" => $name]), E_USER_NOTICE);
        return "";
    }
    $cf_num = $thisfile[$name];
    $cf_val = $thisfile[$cf_num];

    if (!isset($thing)) {
        $thing = $cf_val !== "" ? $cf_val : $default;
    }

    $thing = $escape === null ? txpspecialchars($thing) : parse($thing);

    return !empty($thing) ? doTag($thing, $wraptag, $class) : "";
}

/**
 * Public tag: Check if custom file field exists
 * @param  string $atts[name]    Name of custom field.
 * @param  string $atts[value]   Value to test against (optional).
 * @param  string $atts[match]   Match testing: exact, any, all, pattern.
 * @param  string $atts[separator] Item separator for match="any" or "all". Otherwise ignored.
 * @return string custom field output
 * <code>
 *        <txp:jcr_if_file_custom name="menu_title" /> … <txp:else /> … </txp:jcr_if_file_custom>
 * </code>
 */
function jcr_if_file_custom($atts, $thing = null)
{
    global $thisfile;

    extract(
        $atts = lAtts(
            [
                "name" => get_pref("file_custom_1_set"),
                "value" => null,
                "match" => "exact",
                "separator" => "",
            ],
            $atts
        )
    );

    $name = strtolower($name);

    // Populate file custom field data;
    foreach (jcr_file_column_map() as $key => $column) {
        $thisfile[$key] = isset($column) ? $column : null;
    }

    if (!isset($thisfile[$name])) {
        trigger_error(gTxt("field_not_found", ["{name}" => $name]), E_USER_NOTICE);
        return "";
    }
    $cf_num = $thisfile[$name];
    $cf_val = $thisfile[$cf_num];

    if ($value !== null) {
        $cond = txp_match($atts, $cf_val);
    } else {
        $cond = $cf_val !== "";
    }

    return isset($thing) ? parse($thing, !empty($cond)) : !empty($cond);
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---

# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
h1. jcr_file_custom

Adds up to five extra custom fields of up to 255 characters to the "Content › Files":http://docs.textpattern.io/administration/files-panel panel and provides a corresponding tag to output the custom field and to test if it contains a value or matches a specific value.

h3(#installation). Installation

Paste the code into the _Admin › Plugins_ panel, install and enable the plugin.


h2. Use cases

Use whenever extra information needs to be stored with a file. For example:

* Store a txp image ID number and use it to associate a cover image with the file.
* Store associated details, for example the author(s) of a document, year of issue, duration of a music track …


h2(#tags). Tags

h3. jcr_file_custom

Outputs the content of the file custom field.

h4. Tag attributes

@name@
Specifies the name of the file custom field.
Example: Use @name="copyright_author"@ to output the copyright_author custom field. Default: jcr_file_custom_1.

@escape@
Escape HTML entities such as @<@, @>@ and @&@ prior to echoing the field contents.
Supports extended escape values in txp 4.8
Example: Use @escape="textile"@ to convert textile in the value. Default: none.

@default@
Specifies the default output if the custom field is empty
Example: Use @default="Org Name"@ to output "Org Name", e.g. for when no copyright_author explicitly given. Default: empty.

@wraptag@
Wrap the custom field contents in an HTML tag
Example: Use @wraptag="h2"@ to output @<h2>Custom field value</h2>@. Default: empty.

@class@
Specifies a class to be added to the @wraptag@ attribute
Example: Use @wraptag="p" class="copyright"@ to output @<p class="copyright">Custom field value</p>@. Default: empty

h3. jcr_if_file_custom

Tests for existence of a file custom field, or whether one or several matches a value or pattern.

h4. Tag attributes

@name@
Specifies the name of the file custom field.
Example: Use @name="copyright_author"@ to output the copyright_author custom field. Default: jcr_file_custom_1.

@value@
Value to test against (optional).
If not specified, the tag tests for the existence of any value in the specified file custom field.
Example: Use @value="english"@ to output only those files whose “language” file custom field is english. Default: none.

@match@
Match testing: exact, any, all, pattern. See the docs for "if_custom_field":https://docs.textpattern.com/tags/if_custom_field.
Default: exact.

@separator@
Item separator for match="any" or "all". Otherwise ignored.
Default: empty.


h2(#examples). Examples

1. Produce a list of downloadable documents (assigned to the file category "issues") with their publication covers:

bc. <txp:file_download_list wraptag="ul" break="li" category="issues">
  <a href="<txp:file_download_link />" title="<txp:file_download_name />">
    <txp:image id='<txp:jcr_file_custom name="file_image" />' />
    <txp:jcr_file_custom name="file_issue" wraptag="p" class="issue" />
  </a>
</txp:file_download_list>

p. where the @file_image@ and @file_issue@ file custom fields are used to store the Image ID# of the document cover image and the issue number of the document respectively.

2. Outputs a discography with CD covers, release year and a link to a preview page if one exists:

bc. <txp:file_download_list wraptag="ul" break="li" category="discography">
  <div class="album">
    <a href="<txp:file_download_link />" title="<txp:file_download_name />">
      <txp:image id='<txp:jcr_file_custom name="cd_image" />' />
      <h2 class="album-title"><txp:file_description /> <txp:jcr_file_custom name="cd_year" wraptag="span" class="album-year" /></h2>
    </a>
    <txp:jcr_if_file_custom name="cd_preview_article">
      <a href="<txp:permlink id='<txp:jcr_file_custom name="cd_preview_article" />' />" class="preview-button">Preview></a>
    </txp:jcr_if_file_custom>
  </div>
</txp:file_download_list>

p. where file custom fields have been defined for @cd_image@ (the image ID# of the album cover), @cd_year@ (release year) and @cd_preview_article@ (e.g. the article ID# of a corresponding article with preview of the album).

h2. Changing the label of the custom field

The name of custom field can be changed by specifying a new label using the _Install from Textpack_ field in the "Admin › Languages":http://docs.textpattern.io/administration/languages-panel panel. Enter your own information in the following pattern and click *Upload*:

bc.. #@admin
#@language en
jcr_file_custom_1 => Your label
jcr_file_custom_2 => Your other label

p. replacing @en@ with your own language and @Your label@ with your own desired label.


h2(#deinstallation). De-installation

The plugin cleans up after itself: deinstalling the plugin removes the extra column from the database. To stop using the plugin but keep the database tables, just disable (deactivate) the plugin but don't delete it.


h2(#changelog). Changelog + Credits

h3. Changelog

* Version 0.2.0 – 2020/12/18 – Expand to handle multiple custom fields
* Version 0.1.1 – 2016/12/05 – Remedy table not being created on install
* Version 0.1 – 2016/03/04

h3. Credits

Robert Wetzlmayr’s "wet_profile":https://github.com/rwetzlmayr/wet_profile plugin for the starting point, and further examples by "Stef Dawson":http://www.stefdawson.com and "Jukka Svahn":https://github.com/gocom.
# --- END PLUGIN HELP ---
-->
<?php
}
?>
