widget_tree_picker Contao Extension
===================================

Version 2.3.0 stable (2016-02-17)
---------------------------------

### Improved
- Added the ```orderCallback``` option for sorting the widget records
- Extension is now fully compatible with Contao 4
- Support load_callback in the AJAX generated widget chunks

### Fixed
- Do not throw an error in AJAX request if field does not exist (e.g. data is managed in relational table)


Version 2.2.0 stable (2016-02-15)
---------------------------------

### Fixed
- Do not display manager link in every popup (see #11)

### Improved
- Added the ```selectParents``` option for checkboxes selector
- Updated the composer.json dependencies for Contao 4


Version 2.1.0 stable (2015-12-21)
---------------------------------

### Improved
- Added the new hooks thanks to RockKeeper (see #6)


Version 2.0.2 stable (2015-11-16)
---------------------------------

### Fixed
- Fixed the wrong initialize.php path on composer installation (#9)
- Fixed the generate label method


Version 2.0.1 stable (2015-06-10)
---------------------------------

### Fixed
- Updated the composer.json file


Version 2.0.0 stable (2015-06-10)
---------------------------------

### Improved
- The extension is now compatible with Contao 3.5


Version 1.0.6 stable (2015-03-13)
---------------------------------

### Fixed
- Fixed the value not being displayed if it was not an array

### Improved
- Added the .gitignore file
- Added the minified version of JavaScript file


Version 1.0.5 stable (2015-01-20)
---------------------------------

### Improved
- Added the German language files (thanks to Didier Federer)


Version 1.0.4 stable (2014-12-18)
---------------------------------

### Fixed
- Added the composer.json file
- Updated the readme file


Version 1.0.3 stable (2014-11-10)
---------------------------------

### Fixed
- Fixed the widget not working in a subpalette


Version 1.0.2 stable (2014-09-01)
---------------------------------

### Fixed
- Drop the intval() method from validator


Version 1.0.1 stable (2014-06-30)
---------------------------------

### Fixed
- Fixed the indentation of entries (see #1)


Version 1.0.0 stable (2014-05-07)
---------------------------------

Initial release.