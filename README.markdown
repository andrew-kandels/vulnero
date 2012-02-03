Vulnero 0.1
==================

This is the list of changes for the 0.1 release series.

Vulnero 0.1.2
------------------
* Refactored widgets slightly to simplify the capturing of options
* Another major routing overhaul, removed the concepts of layouts, using 100% WP page templates
* Renamed some files to better explain their purpose
* Unit tests and documentation updated to describe the latest functionality
* Moving caching, environment and bootstrap option configuration to the default admin page
* Added Zend Framework submodule to simplify installation if it's not in your include_path

Vulnero 0.1.1
------------------
* Major routing improvements and better use of the request and response objects
* Unit tests covering all present functionality in its entirety
* Move all Vulnero logic to library/Vulnero, separating from the Application
* New website with public source code serving as a sample application
* Online documentation
* API class separating WordPress API methods for better testability
* Better control over layouts and WordPress templates
* Simplifying installation

Vulnero 0.1.0
------------------
* First upload for the project.
* Includes the basic WordPress plugin structure and application folders.
* Extends WordPress routing with Zend application routing
* Bootstraps database, routing, config, layouts, views, database
* Controller view scripts display content within WordPress page templates as layouts
* Partial auth implementation
* Setup config and routes ini configuration files
* Setup default and error controllers
* Setup formatted exception output
