# jcr_file_custom

Adds up to five extra custom fields of up to 255 characters to the
[Content › Files](http://docs.textpattern.io/administration/files-panel)
panel along with corresponding tags to output the custom field and to
test if it contains a value or matches a specific value.

### Use cases

Use whenever extra information needs to be stored with a file. For
example:

-   Store a txp image ID number and use it to associate a cover image
    with the file.
-   Store associated details, for example the author(s) of a document,
    year of issue, duration of a music track ...

## Installation / Deinstallation / Upgrading

### Installation

Paste the `.txt` installer code into the *Admin › Plugins* panel, or
upload the plugin's `.php` file via the *Upload plugin* button, then
install and enable the plugin.

### Upgrading

The plugin automatically migrates custom field data and the database structure from the earlier single custom field variant (v0.1) to the new format. No changes are needed to the public tags as the new default settings correspond to the old tag. Nevertheless, it is always advisable to make a database backup before upgrading.

### De-installation

The plugin cleans up after itself: deinstalling (deleting) the plugin
removes the extra columns from the database as well as custom field
names and labels. To stop using the plugin but keep the custom field
data in the database, just disable (deactivate) the plugin but don't
delete it.

## Plugin tags

### jcr_file_custom

Outputs the content of the file custom field.

#### Tag attributes

`name`\
Specifies the name of the file custom field.\
Example: Use `name="copyright_author"` to output the copyright_author
custom field. Default: jcr_file_custom_1.

`escape`\
Escape HTML entities such as `<`, `>` and `&` prior to echoing the field
contents.\
Supports extended escape values in txp 4.8\
Example: Use `escape="textile"` to convert textile in the value.
Default: none.

`default`\
Specifies the default output if the custom field is empty\
Example: Use `default="Org Name"` to output "Org Name", e.g. for when no
copyright_author explicitly given. Default: empty.

`wraptag`\
Wrap the custom field contents in an HTML tag\
Example: Use `wraptag="h2"` to output `<h2>Custom field value</h2>`.
Default: empty.

`class`\
Specifies a class to be added to the `wraptag` attribute\
Example: Use `wraptag="p" class="copyright"` to output
`<p class="copyright">Custom field value</p>`. Default: empty

### jcr_if_file_custom

Tests for existence of a file custom field, or whether one or several
matches a value or pattern.

#### Tag attributes

`name`\
Specifies the name of the file custom field.\
Example: Use `name="copyright_author"` to output the copyright_author
custom field. Default: jcr_file_custom_1.

`value`\
Value to test against (optional).\
If not specified, the tag tests for the existence of any value in the
specified file custom field.\
Example: Use `value="english"` to output only those files whose
"language" file custom field is english. Default: none.

`match`\
Match testing: exact, any, all, pattern. See the docs for
[if_custom_field](https://docs.textpattern.com/tags/if_custom_field).\
Default: exact.

`separator`\
Item separator for match="any" or "all". Otherwise ignored.\
Default: empty.

## Examples

### Example 1

Produce a list of downloadable documents (assigned to the file category
"issues") with their publication covers:

    <txp:file_download_list wraptag="ul" break="li" category="issues">
      <a href="<txp:file_download_link />" title="<txp:file_download_name />">
        <txp:image id='<txp:jcr_file_custom name="file_image" />' />
        <txp:jcr_file_custom name="file_issue" wraptag="p" class="issue" />
      </a>
    </txp:file_download_list>

where the `file_image` and `file_issue` file custom fields are used to
store the Image ID\# of the document cover image and the issue number of
the document respectively.

### Example 2

Outputs a discography with CD covers, release year and a link to a
preview page if one exists:

    <txp:file_download_list wraptag="ul" break="li" category="discography">
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

where file custom fields have been defined for `cd_image` (the image
ID\# of the album cover), `cd_year` (release year) and
`cd_preview_article` (e.g. the article ID\# of a corresponding article
with preview of the album).

## Custom field labels

The label displayed alongside the custom field in the edit file panel
can be changed by specifying a new label using the *Install from
Textpack* field in the [Admin ›
Languages](http://docs.textpattern.io/administration/languages-panel)
panel. Enter your own information in the following pattern and click
**Upload**:

    #@owner jcr_file_custom
    #@language en, en-gb, en-us
    #@file
    jcr_file_custom_1 => Your label
    jcr_file_custom_2 => Your other label
    …

replacing `en` with your own language and `Your label` with your own
desired label.

## Changelog and credits

### Changelog

-   Version 0.2.0 -- 2020/12/18 -- Expand to handle multiple custom
    fields
-   Version 0.1.1 -- 2016/12/05 -- Remedy table not being created on
    install
-   Version 0.1 -- 2016/03/04

### Credits

Robert Wetzlmayr's
[wet_profile](https://github.com/rwetzlmayr/wet_profile) plugin for the
starting point, and further examples by [Stef
Dawson](http://www.stefdawson.com) and [Jukka
Svahn](https://github.com/gocom).
