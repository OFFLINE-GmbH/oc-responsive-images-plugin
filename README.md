# Responsive Images Plugin for October CMS

## How it works

This plugin provides a middleware that adds `srcset` and `sizes` attributes to all locally served images in your html
 response.
 
It automatically creates resized copies of the image and serves the most fitting one to your visitor.
  
Currently three image sizes are created: 300, 768 and 1024 pixels.

Configuration possibilities for these values will be added in a future release.
 
All image copies are saved in your uploads path. Remote file systems are currently untested.

## Todo

* Unit Tests
* Configuration
* Exclude/Include-Filters
* Maybe a component to include the middleware only on some pages